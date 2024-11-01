jQuery(document).ready(function($) {

	$('#add_btn').click(function() {
		var cnt = $('#script-table').find('textarea').length;
		var original = $('#script-tr-' + (cnt-1));
		$(original)
		.clone(true)
		.insertAfter(original)
		.attr('id', 'script-tr-' + cnt) // 最終行をクローンしてID属性を変更

		$('tr#script-tr-' + cnt + ' th').text(cnt+1);
		$('tr#script-tr-' + cnt + ' td textarea').attr('name', 'script_' + cnt);
		$('tr#script-tr-' + cnt + ' td textarea').val("");
	});

	$('.del_btn').click(function() {
		var cnt = $('#script-table').find('textarea').length;
		if(cnt <= 1){
			alert("Do not delete!");
			return false;
		}

		var tr = $(this).parent().parent();
		$(tr).remove();

		// TRのIDを順番に振りなおす
		$('#script-table tr').each(function (i) {
			if(i < (cnt - 1)){
				$(this).attr('id', 'script-tr-' + i);
			}
		});

		// TR子要素の値やNAMEを振りなおす
		$('#script-table tr').each(function (i) {
			$('tr#script-tr-' + i + ' th').text(i+1);
			$('tr#script-tr-' + i + ' td textarea').attr('name', 'script_' + i);
		});
	});

});