module.exports.splitnum = function(index_count,splitNum,block) {
		index_count = parseInt(index_count);
		if( index_count%splitNum === 0 && index_count !== 0 ){
			return block.fn(this);
		}
	};