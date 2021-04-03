$(document).ready(function(){
	// ドラッグしたままエリアに乗った＆外れたとき
	$("#file_drag_drop_area").on('dragover', function(event){
		event.preventDefault();
		event.stopPropagation();
		$(this).css('background-color', '#999999');
	});
	$("#file_drag_drop_area").on('dragleave', function(event){
		event.preventDefault();
		event.stopPropagation();
		$(this).css('background-color', 'transparent');
	});
	// ドロップした時
	$("#file_drag_drop_area").on('drop', function(event){
		let original_event = event;
		if(event.originalEvent){
			original_event = event.originalEvent;
		}

		original_event.preventDefault();
		file_input.files = original_event.dataTransfer.files;
		$(this).css('background-color', 'transparent');
	});
});