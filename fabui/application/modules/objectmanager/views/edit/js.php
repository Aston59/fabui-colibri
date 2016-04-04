<script type="text/javascript">

var responsiveHelper_dt_basic = undefined;
var oTable;
var objectId = <?php echo $_id_object; ?>;
var printableFiles = ['.gc', '.gcode', '.nc'];
var previewFiles = ['.stl', '.gc', '.gcode'];
var printUrl = '<?php echo site_url('make') ?>';
var breakpointDefinition = {
			tablet : 1024,
			phone : 480
};

$(function() {
	
	$("#files_table tbody > tr").on("click", function(e) {
		
		var tag = e.target.nodeName;
		
		if(tag == 'TD' || tag == "I"){
		
			var nTr = $(this);
			
			var aCell = $(this).find("a i[data-toggle='row-detail']");
			
			
			
			if (oTable.fnIsOpen(nTr)) {
				/* This row is already open - close it */
				
				console.log("già aperto");
				
				aCell.removeClass("fa-chevron-down").addClass("fa-chevron-right");
				this.title = "Show Details";
				nTr.removeClass("info");
				oTable.fnClose(nTr);
				$("[rel=tooltip], [data-rel=tooltip]").tooltip();
			} else {
				/* Open this row */
				nTr.addClass("info");
				aCell.removeClass("fa-chevron-right").addClass("fa-chevron-down");
				oTable.fnOpen(nTr, fnFormatDetails(oTable, nTr), "details");
				$("[rel=tooltip], [data-rel=tooltip]").tooltip();
			}
			return false;
		
		}
		
		
	});
	

	oTable = $("#files_table").dataTable({
		"aaSorting": [],
		"sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
				"t"+
				"<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
		"autoWidth": false,
		"preDrawCallback" : function() {
				if (!responsiveHelper_dt_basic) {
					responsiveHelper_dt_basic = new ResponsiveDatatablesHelper($('#files_table'), breakpointDefinition);
				}
		},
		"rowCallback" : function(nRow) {
				responsiveHelper_dt_basic.createExpandIcon(nRow);
		},
			"drawCallback" : function(oSettings) {
				responsiveHelper_dt_basic.respond();
		}
	});

	function fnFormatDetails(oTable, nTr) {
		var aData = oTable.fnGetData(nTr);
		var fileInfo = aData[0].split('-');
		
		var fileId   = aData[0];
		var fileName = aData[1];
		var fileExt  = aData[2];
		var fileType = aData[3];
		
		
		
		
		var editUrl = '<?php echo site_url('objectmanager/file/view/') ?>/' + objectId + '/' + fileId;
		var downloadUrl = '<?php echo site_url('objectmanager/download/file/') ?>/' + fileId ;
		var previewUrl = '<?php echo site_url('objectmanager/file/preview/') ?>/' + objectId + '/' + fileId ;
		var statsUrl = '<?php echo site_url('objectmanager/file/stats/') ?>/' + objectId + '/' + fileId;
		
		var edit_button = '<a rel="tooltip" data-placement="bottom" data-original-title="Edit the file" href="' + editUrl + '" class="btn btn-primary btn-xs details-button"><i class="fa fa-pencil"></i> <span class="hidden-xs">Edit</span></a>';
		var print_button = '';
		var preview_button = '';
		var stats_button = '';		
		
		
		var delete_button = ' <a rel="tooltip" data-placement="bottom" data-original-title="Delete the file" href=\'javascript:ask_delete(' + fileId +', "' + fileName + '");\' data-file-id="' + fileId + '" class="btn btn-danger details-button btn-xs  delete-file"><i class="fa fa-trash"></i> <span class="hidden-xs">Delete</span></a>';
		var download_button = '<a rel="tooltip" data-placement="bottom"  data-original-title="Save data on your computer. You can use it in the third party software." href="' + downloadUrl + '" class="btn btn-info btn-xs details-button"><i class="fa fa-download"></i> <span class="hidden-xs">Download</span></a>';
		
		
		
		if(printableFiles.indexOf(fileExt.toLowerCase()) > -1){
			
			printUrl = fileType == 'additive' ? '<?php echo site_url('make/print') ?>' : '<?php echo site_url('make/mill') ?>';
			printUrl += '?obj='+objectId+'&file='+fileId;
				
			var printLabel = fileType == 'additive' ? 'Print' : 'Mill';			
			print_button = '<a rel="tooltip" data-placement="bottom" data-original-title="' + printLabel  + ' this file" href="' + printUrl + '" class="btn btn-success btn-xs details-button"><i class="fa fa-play fa-rotate-90"></i> <span class="hidden-xs">'+ printLabel +'</span></a>';
			
			stats_button = '<a class="btn btn-warning btn-xs" href="' +statsUrl+ '"><i class="fa fa-area-chart"></i> <span class="hidden-xs">Stats</span></a>';
				
		}
		
		if(previewFiles.indexOf(fileExt.toLowerCase()) > -1){
			
			var endTitle = fileExt.toLowerCase() == '.stl' ? 'for STL files' : 'for GCode files.';
			preview_button = '<a rel="tooltip" data-placement="bottom" data-original-title="A web-based 3D viewer ' + endTitle+'" href="'+ previewUrl +'" class="btn btn-xs bg-color-purple txt-color-white details-button"><i class="fa fa-eye"></i> <span class="hidden-xs">Preview</span></a>';
		}
		
		return ' '+ edit_button + download_button + preview_button + stats_button +  delete_button + '';
	}

	$('#save-object').on('click', save_object);
	$(".bulk-button").on('click', bulk_actions);
	
	$(".select-all").on('click', function(){
		var that = this;
		$(this).closest("table").find("tr > td input:checkbox").each(function() {
			this.checked = that.checked;			
		});   		
	 });	  
});




function save_object(){
	
	$("#save-object").addClass('disabled');
	$('#save-object').html('<i class="fa fa-save"></i> Saving...');
	
	$.ajax({
		type: "POST",
		url: "<?php echo module_url('objectmanager').'ajax/save_object.php' ?>",
		data: {object_id : <?php echo $_object->id ?>, name: $("#obj_name").val(), description: $("#obj_description").val(), private: $('[name="private"]:checked').val()},
		dataType: 'json'
	}).done(function(response) {

		$("#save-object").removeClass('disabled');
		$('#save-object').html('<i class="fa fa-save"></i> Save');
		
		
		$.smallBox({
			title : "Object saved with success",
			color : "#659265",
			iconSmall : "fa fa-check bounce animated",
			timeout : 4000
		});

		$("#label-obj-name").html($("#obj_name").val());
				
	});
	
}


function ask_delete(id_file, file_name) {

	$.SmartMessageBox({
		title: "Attention!",
		content: "Remove <b>" + file_name + "</b> ?",
		buttons: '[No][Yes]'
	}, function(ButtonPressed) {
	   
		if (ButtonPressed === "Yes") {

			delete_file(id_file);
		}
		if (ButtonPressed === "No") {

		}

	});

}



function delete_file(id_file) {
    
    openWait('Deleting file..');
    
    var ids = new Array();
    ids.push(id_file);
    
	delete_files(ids);

}


function bulk_actions(){
		
	var action = $( this ).attr('data-action');
	
	if(action == ""){
		show_message("Please select an action");
		return false;
	}
	
	switch(action){
		case 'delete':
			bulk_delete();
			break;
		case 'download':
			bulk_download();
			break;
	}	
		   		
}


function delete_files(list){
    	
	$(".bulk-button").addClass("disabled");
	$(".bulk-button").html("Deleting...");
	
	$.ajax({
			type: "POST",
			url: "<?php echo site_url('objectmanager/delete_file') ?>",
			dataType: 'json',
			data: {ids: list}
		}).done(function(response) {

			if (response.success == true) {
            
            	document.location.href = document.location.href;

			} else {
	
				show_error(response.message);
			}
			
		});

}


function bulk_delete(){
    	
    	
	var ids = new Array();
	
	var boxes = $(".table tbody").find(":checkbox:checked");
	
	if(boxes.length > 0){
		   		
		boxes.each(function() {
			ids.push($(this).attr("id").replace("check_", ""));
		});
		
		bulk_ask_delete(ids);			

	}else{
		 show_message("Please select at least 1 file");
		 return false;  			
	}
	
	
}


function show_message(message){
    	
    	$.SmartMessageBox({
				title: "<i class='fa fa-info-circle'></i> Information",
				content: message,
				buttons: '[Ok]'
			}, function(ButtonPressed) {
				if (ButtonPressed === "OK") {
				}
				
			});
    	
}
    
    
function bulk_ask_delete(ids){
    	
	$.SmartMessageBox({
			title: "<i class='fa fa-warning txt-color-orangeDark'></i> Warning!",
			content: "Do you really want to remove the selected files",
			buttons: '[No][Yes]'
		}, function(ButtonPressed) {
			if (ButtonPressed === "Yes") {

				delete_files(ids);
			}
			if (ButtonPressed === "No") {

			}

		});
}


	function bulk_download(){
    	var ids = new Array();
    	var boxes = $(".table tbody").find(":checkbox:checked");
    	
    	if(boxes.length > 0){
    		   		
    		boxes.each(function() {
				ids.push($(this).attr("id").replace("check_", ""));
			});		
			bulk_ask_download(ids);			

    	}else{
    		 show_message("Please select at least 1 object");
    		 return false;  			
    	}
    }
    
    
    
    function bulk_ask_download(ids){
    	$.SmartMessageBox({
				title: "<i class='fa fa-warning txt-color-orangeDark'></i> Warning!",
				content: "Do you really want download the selected files",
				buttons: '[No][Yes]'
		}, function(ButtonPressed) {
			if (ButtonPressed === "Yes") {
				download_files(ids);
			}
			if (ButtonPressed === "No") {

			}
		});
    }
    
    
    function download_files(list){  	
    	document.location.href = '<?php echo site_url('objectmanager/download/file/') ?>/' + list.join('-');

    }
        



</script>
