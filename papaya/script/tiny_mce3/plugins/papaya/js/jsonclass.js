var JsonClass = function() {
	var x = arguments;
	var constr = function() {
		var a = arguments;
    var u;
		for (var i = 0; i < x.length; i++) {
			if (typeof x[i] == "object"){
				for (var j in x[i]) {
          this[j] = this[j] != u ? this[j] : x[i][j];
        }
			}
			if (typeof x[i] != "string" || a[i] === u) {
        continue;
      }
			this[x[i]] = a[i];
		}
	};

	var nextProto = false;
	for (var i = 0; i <= x.length; i++) {
		var a = x[i] || constr;
		if (typeof a == "function") {
			a.prototype = nextProto || a.prototype;
			nextProto = new a();
		}
	}

	constr.addMembers = function(obj) {
		for (var i in obj) {
      constr.prototype[i] = obj[i];
    }
	};

	return constr;
};