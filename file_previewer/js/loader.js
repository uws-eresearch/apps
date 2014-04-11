function hideDOCviewer() {
	$('#content table').show();
	$("#controls").show();
	$("#editor").show();
	$('iframe').remove();
	$('a.action').remove();
}

showPreview.oldCode='';
showPreview.lastTitle='';
var oldcontent = '';

function showPreview(dir,filename, type){
		
	var viewer = getFilePath(dir, filename, type);
		
	$.fancybox({
        'autoScale'     : false,
        'transitionIn'  : 'none',
        'transitionOut' : 'none',
        'title'         : this.title,
        'width'     : '75%',
        'height'        : '75%',
        'href'          : viewer,
        'type'          : 'iframe'
    });
		
}

/*$(window).on("hashchange", function() {
    if (!/#preview/.test(window.location.hash)) {
    	$('#content').html(oldcontent);
		$("#editor").show();
		$('#content table').show();
		//$("#controls").show();
		oldcontent = '';
    }
  });*/

function getFilePath(dir, filename, prev_type) {
	var baseUrl = '';
	if(dir === '/'){
		baseUrl = dir + '_html/' + filename + '/index.html';	
	}
	else{
		baseUrl = dir + '/_html/' + filename + '/index.html';
	}
	var viewer = OC.Router.generate('preview_handler', { fname: baseUrl});
	//var viewer = OC.linkTo('file_previewer', 'docViewer.php')+'?fname='+baseUrl+'&type='+prev_type;
	return viewer;
}

function getRequestURL(dir, filename, type) {
	var baseUrl = '';
	if(dir === '/'){
		baseUrl = dir + filename + '/';	
	}
	else{
		baseUrl = dir + '/' + filename + '/';
	}
	var idx = filename.lastIndexOf(".");
	var url = baseUrl + filename.slice(0, idx) + type;
	var viewer = OC.Router.generate('previewer', { fname: url});
	return viewer;
}

$(document).ready(function() {
	if(!$.browser.msie){//doesn't work on IE
		//if(location.href.indexOf("files")!=-1) {
			if(typeof FileActions!=='undefined'){
				var supportedMimes = new Array(
					'application/msword',
					'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
					'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
					'application/msexcel',
					'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
					'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
					'application/mspowerpoint',
					'application/vnd.openxmlformats-officedocument.presentationml.presentation',
					'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
					'application/vnd.openxmlformats-officedocument.presentationml.template',
					'application/vnd.openxmlformats-officedocument.presentationml.slide',
					'application/vnd.oasis.opendocument.text', 
					'application/vnd.oasis.opendocument.spreadsheet',
					'application/vnd.oasis.opendocument.graphics',
					'application/vnd.oasis.opendocument.presentation');
				for (var i = 0; i < supportedMimes.length; ++i){
					var mime = supportedMimes[i];
					FileActions.register(mime,'Preview',OC.PERMISSION_READ,'',function(filename){
						showPreview($('#dir').val(),filename, 'html');
					});
					/*FileActions.register(mime,'Prev',OC.PERMISSION_READ,'',function(filename){
						showPreview($('#dir').val(),filename, 'pdf');
					});
					FileActions.setDefault(mime,'Prev');*/
				}
			}
		//}
		
		//if(location.href.indexOf("files")!=-1) {
			if(typeof FileActions!=='undefined') {
				FileActions.register('application/msword','ePub', OC.PERMISSION_READ, '',function(filename) {
					//window.location = OC.linkTo('file_previewer', 'docViewer.php')+'?dir='+encodeURIComponent($('#dir').val()).replace(/%2F/g, '/')+'&file='+encodeURIComponent(filename.replace('&', '%26'))+'&type=epub';
					window.location = getRequestURL($('#dir').val(), filename, '.epub');
				});
			}
		//}
		
		
	}
});
