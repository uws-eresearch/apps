/**
 * ownCloud - Cr8it App
 *
 * @author Lloyd Harischandra
 * @copyright 2014 University of Western Sydney www.uws.edu.au
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

function getFileName(dir, filename) {
	var baseUrl = '';
	if(dir === '/'){
		baseUrl = filename;	
	}
	else{
		baseUrl = dir.replace(/^\//g,'') + '/' + filename;
	}
	return baseUrl;
}

$(document).ready(function(){
	if(!$.browser.msie){//doesn't work on IE
		
		if(location.href.indexOf("files")!=-1) {
			if(typeof FileActions!=='undefined'){
				FileActions.register('all','Add to crate', OC.PERMISSION_READ, '',function(filename){
					$.ajax({url: OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?file='+getFileName($('#dir').val(),filename)+'&action=add',
						type: 'get',
						dataType: 'text/html',
						complete: function(data){
							OC.Notification.show(data.responseText);
							setTimeout(function() {OC.Notification.hide();}, 3000);
						}
					});
				});
			}
		}
		
		
	}
});

function addToTree(file){
	//$.ajax({url: OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?dir='+encodeURIComponent($('#dir').val())
		//.replace(/%2F/g, '/')+'&file='+encodeURIComponent(filename.replace('&', '%26'))+'&action=add'
}
