/* Namespace: ArtMaps */
var ArtMaps = ArtMaps || (function(){
    
    /* Extend google.maps.MVCObject */
    google.maps.MVCObject.prototype.on = function(eventName, handler) {
        return google.maps.event.addListener(this, eventName, handler);
    };
    google.maps.MVCObject.prototype.off = function(listener) {
        google.maps.event.removeListener(listener);
    };

    /* Extend MarkerClusterer */
    MarkerClusterer.prototype.on = function(eventName, handler) {
        return google.maps.event.addListener(this, eventName, handler);
    };
    MarkerClusterer.prototype.off = function(listener) {
        google.maps.event.removeListener(listener);
    };

    return new Object();
}());

ArtMaps.RunOnce = function(script) {
    var workers = new Array(new Worker(script));
    var queuedTask = false;
    
    var runTask = function(worker, data, pre, callback) {
        pre();
        var l = null;
        l = function(e) {
            worker.removeEventListener("message", l, false);
            callback(e.data);
            if(queuedTask) {
                runTask(worker, queuedTask.data, queuedTask.pre, queuedTask.callback);
            } else {
                workers.push(worker);
            }
            queuedTask = false;
        };
        worker.addEventListener("message", l, false);
        worker.postMessage(data);
    };
    
    this.queueTask = function(data, pre, callback) {
        var w = workers.pop();
        if (w) {
            runTask(w, data, pre, callback);
        } else {
            queuedTask = {
                "data": data,
                "pre": pre,
                "callback": callback
            };
        }
    };
};

ArtMaps.WorkerPool = function(size, script) {

    var availableWorkers = [];
    var queuedTasks = [];
    for(var i = 0; i < size; i++) {
        availableWorkers.push(new Worker(script));
    }

    var runTask = function(worker, data, callback) {
        var l = null;
        l = function(e) {
            worker.removeEventListener("message", l, false);
            callback(e.data);
            var next = queuedTasks.pop();
            if(next) {
                runTask(worker, next.data, next.callback);
            } else {
                availableWorkers.push(worker);
            }
        };
        worker.addEventListener("message", l, false);
        worker.postMessage(data);
    };

    this.queueTask = function(data, callback) {
        var w = availableWorkers.pop();
        if (w) {
            runTask(w, data, callback);
        } else {
            queuedTasks.push({
                "data": data,
                "callback": callback
            });
        }
    };
};

ArtMaps.Location = function(l, o, as) {
    this.ID = l.ID;
    this.Source = l.source;
    this.Latitude = ArtMaps.Util.toFloatCoord(l.latitude);
    this.Longitude = ArtMaps.Util.toFloatCoord(l.longitude);
    this.Error = l.error;
    this.ObjectOfInterest = o;
    this.Actions = as;
    this.Confirmations = 0;
    this.OwnerID = -1;
    this.IsDeleted = false;
    this.UsersWhoConfirmed = [];
    
    // Find the number of confirmations
    var l = as.length;
    for(var i = 0; i < l; i++) {
        if(as[i].URI.indexOf("confirmation") == 0) {
            this.Confirmations++;
            this.UsersWhoConfirmed.push(as[i].userID);
        }
        if(as[i].URI.indexOf("suggestion") == 0)
            this.OwnerID = as[i].userID;
        if(as[i].URI.indexOf("deletion") == 0)
            this.IsDeleted = true;
    }
};

ArtMaps.ObjectOfInterest = function(o) {
    
    var self = this;
    this.ID = o.ID;
    this.URI = o.URI;
    this.Locations = [];
    this.SuggestionCount = 0;
    
    // Sort actions by location
    var abl = {};
    var re = /^.*LocationID"\s*:\s*(\d+).*$/;
    var l = o.actions.length;
    for(var i = 0; i < l; i++) {
        var a = o.actions[i];
        var lid = a.URI.replace(re, "$1");
        if(!abl[lid]) abl[lid] = new Array();
        var arr = abl[lid];
        arr[arr.length] = a;
    }
    // Sort actions into timestamp order (ascending)
    for(var as in abl)
        abl[as].sort(function(a, b) {
            return a.timestamp - b.timestamp;
        });
    // Create location objects
    l = o.locations.length;
    for(var i = 0; i < l; i++) {
        var loc = o.locations[i];
        var as = abl[loc.ID] ? abl[loc.ID] : [];
        this.Locations[this.Locations.length] = new ArtMaps.Location(loc, this, as);
        if(loc.source != "SystemImport") this.SuggestionCount++;            
    }
    
    this.Metadata = function(func) {
        this.workerPool.queueTask(
                ArtMapsConfig.CoreServerPrefix + "objectsofinterest/" + o.ID + "/metadata",
                function(data) {
                    self.Metadata = data;
                    self.Metadata = function(f) {
                        f(self.Metadata);
                    };
                    func(data); 
                }
        );
    };
};
ArtMaps.ObjectOfInterest.prototype.workerPool = 
        new ArtMaps.WorkerPool(20, ArtMapsConfig.PluginDirUrl + "/js/do-get.js");
