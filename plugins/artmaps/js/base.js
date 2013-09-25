/* Namespace: ArtMaps */
var ArtMaps = ArtMaps || (function(){
    
    /* Extend google.maps.MVCObject */
    google.maps.MVCObject.prototype.on = function(eventName, handler) {
        return google.maps.event.addListener(this, eventName, handler);
    };
    google.maps.MVCObject.prototype.off = function(listener) {
        google.maps.event.removeListener(listener);
    };
    google.maps.MVCObject.prototype.trigger = function(eventName) {
        google.maps.event.trigger(this, eventName);
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

ArtMaps.WorkerRunOnce = function(script) {
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

ArtMaps.AltRunOnce = function(func) {
    var workers = new Array(func);
    var queuedTask = false;
    
    var runTask = function(worker, data, pre, callback) {
        pre();
        worker(data, function(msg) {
            callback(msg);
            if(queuedTask) {
                runTask(worker, queuedTask.data, queuedTask.pre, queuedTask.callback);
            } else {
                workers.push(worker);
            }
            queuedTask = false;
        });
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

ArtMaps.RunOnce = function(workerScript, altFunc) {
    var inner = null;
    if(typeof(Worker) === 'undefined') {
        inner = new ArtMaps.AltRunOnce(altFunc);
    } else {
        inner = new ArtMaps.WorkerRunOnce(workerScript);
    }
    
    this.queueTask = function(data, pre, callback) {
        inner.queueTask(data, pre, callback);
    };
};

ArtMaps.WorkerWorkerPool = function(size, script) {

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

ArtMaps.AltWorkerPool = function(size, func) {

    var availableWorkers = [];
    var queuedTasks = [];
    for(var i = 0; i < size; i++) {
        availableWorkers.push({ "func": func });
    }

    var runTask = function(worker, data, callback) {
        worker.func(data, function(msg) {
            callback(msg);
            var next = queuedTasks.pop();
            if(next) {
                runTask(worker, next.data, next.callback);
            } else {
                availableWorkers.push(worker);
            }
        });
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

ArtMaps.WorkerPool = function(size, workerScript, altFunc) {
    var inner = null;
    if(typeof(Worker) === 'undefined') {
        inner = new ArtMaps.AltWorkerPool(size, altFunc);
    } else {
        inner = new ArtMaps.WorkerWorkerPool(size, workerScript);
    }
    
    this.queueTask = function(data, pre, callback) {
        inner.queueTask(data, pre, callback);
    };
};

ArtMaps.Location = function(l, o, as) {
    var self = this;
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
    this.CommentID = -1;
    this.IsSuggestion = false;
    this.IsFinal = false;
    this.FinalAction = null;
    
    var refresh = function() {
        var l = as.length;
        for(var i = 0; i < l; i++) {
            if(as[i].URI.indexOf("confirmation") == 0) {
                self.Confirmations++;
                self.UsersWhoConfirmed.push(as[i].userID);
            }
            if(as[i].URI.indexOf("suggestion") == 0) {
                self.OwnerID = as[i].userID;
                self.IsSuggestion = true;
            }
            if(as[i].URI.indexOf("deletion") == 0)
                self.IsDeleted = true;
            if(as[i].URI.indexOf("comment") == 0) {
                var d = JSON.parse(as[i].URI.replace("comment://", ""));
                self.CommentID = d.CommentID;
            }
            if(as[i].URI.indexOf("finalisation") == 0)
                self.FinalAction = as[i];
        }
    };
    refresh();
    
    this.addAction = function(action) {
        self.Actions[self.Actions.length] = action;
        refresh();
        self.ObjectOfInterest.refresh();
    };
    
    this.hasUserConfirmed = function(userID) {
        return jQuery.inArray(parseInt(userID), this.UsersWhoConfirmed) > -1;
    };
};

ArtMaps.ObjectOfInterest = function(o) {
    
    var self = this;
    this.ID = o.ID;
    this.URI = o.URI;
    this.Locations = [];
    this.SuggestionCount = 0;
    this.HasComments = false;
    
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
    }
    
    this.refresh = function() {
        // Count active suggestions
        // Find the most recent finalisation action
        var finalloc = null;
        l = this.Locations.length;
        for(var i = 0; i < l; i++) {
            var loc = this.Locations[i];
            loc.IsFinal = false;
            if(loc.IsDeleted) continue;
            if(loc.CommentID > -1) self.HasComments = true;
            if(loc.IsSuggestion) this.SuggestionCount++;
            if(finalloc == null) {
                finalloc = loc;
                continue;
            }
            if(finalloc.FinalAction == null && loc.FinalAction != null) {
                finalloc = loc;
                continue;
            }
            if(finalloc.FinalAction != null && loc.FinalAction != null
                    && loc.FinalAction.datetime > finalloc.FinalAction.datetime) {
                finalloc = loc;
                continue;                        
            }
        }
        if(finalloc != null && finalloc.FinalAction != null)
            finalloc.IsFinal = true;
    };
    this.refresh();
    
    this.Metadata = function(func) {
        this.workerPool.queueTask(
                ArtMapsConfig.CoreServerPrefix + "objectsofinterest/" + o.ID + "/metadata",
                function(data) {
                    self.metadata = data;
                    self.Metadata = function(f) {
                        f(self.metadata);
                    };
                    func(data); 
                }
        );
    };
};
/* N.B Some versions of Firefox (e.g. 24.0) appear to have a maximum limit of 20 webworkers */
ArtMaps.ObjectOfInterest.prototype.workerPool = 
        new ArtMaps.WorkerPool(15, ArtMapsConfig.PluginDirUrl + "/js/do-get.js",
                function(data, callback) {
                    jQuery.getJSON(data, function(j) {
                        callback(j);
                    });
                });
