module.exports.supordinalize = function(int) {

		var a = ['t','r','s','n'];
		var b,c;
		var d;

		a.forEach(function(el,i){
			if ( int.indexOf(el) !== -1 ) {
				b = int.split(el);
				c = String(parseInt(b[0])).length;
				if ( int.indexOf(el) === c ) {
					d = b[0]+'<sup>'+el+b[1]+'</sup>';
				}
			}
		});

		return d;

	};