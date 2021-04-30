

window.addEvent('load', function(){
	var layoutsMenu = new ContextMenu({
		menu:	'layoutsMenu',
		targets: '#layoutsControl',
		trigger: 'click',
		actions: {
			'save' : function(){
				layoutsUpdate('edit', Cookie.read('currentLayout'));
			},
			'saveAs' : function(){
				var layoutName = prompt('New layout name:');
				if (layoutName != null){
					layoutsUpdate('new', layoutName);
				}
			},
			'delete' : function(){
				layoutsUpdate('delete', Cookie.read('currentLayout'));
				Cookie.write('currentLayout', '');
			},
			'load' : function(el, ref, item){
				layoutsUpdate('load', item.get('html'));
				Cookie.write('currentLayout', item.get('html'));
			},
			'clearAll' : function(){
				layoutsUpdate('clear', false);
				Cookie.write('currentLayout', '');
			}
		}
	});
	makeGrid();
	adjustImageSize();
	var cameraMenu = new ContextMenu({
		menu:	'cameraList',
		targets: '.noImg',
		trigger: 'click',
		actions: {
			'loadCam' : function(el, ref, item){
				var id = item.get('id');
				var elParent = el.getParent();
				Cookie.write('imgSrc'+elParent.getParent().get('class')+elParent.get('class'), id);
				el.set('src', '/media/mjpeg?multipart=true&id='+id);
				if (item.get('class')){
					addPtz(el, id);
				};
			}
		}
	});
	$$('.ac').addEvent('click', function(){ addDelRowColumn('c', true); });
	$$('.dc').addEvent('click', function(){ addDelRowColumn('c', false); });
	$$('.ar').addEvent('click', function(){ addDelRowColumn('r', true); });
	$$('.dr').addEvent('click', function(){ addDelRowColumn('r', false); });
	
	$$('.l1').addEvent('click', function(){ setLayout(1);	});
	$$('.l4').addEvent('click', function(){ setLayout(4);	});
	$$('.l9').addEvent('click', function(){ setLayout(9);	});
	$$('.l16').addEvent('click', function(){ setLayout(16); });
	$('logout').addEvent('click', function(){
		document.location = '/logout';
	});
	$('profilePage').addEvent('click', function(){
		document.location = '/profile';
	});
	$('playbackPage').addEvent('click', function(){
		document.location = '/playback';
	});
	$('backToAdmin').addEvent('click', function(){
		document.location = '/';
	});
	$$('.presets').each(function(el){
		presetmenuvar = new ContextMenu({
					menu: 'presets-'+el.getParent().get('id'),
					targets: '#'+el.get('id'),
					trigger: 'click',
					actions:{
						'goto': function(el, ref, item){
							presetRequest('go', item.get('id'), item.get('name'));
						},
						'rename': function(el, ref, item){
							var presetName = prompt('New preset name:');
							if (presetName != null){
								presetRequest('rename', item.get('id'), item.get('name'), presetName);
							}
						},
						'delete': function(el, ref, item){
							presetRequest('clear', item.get('id'), item.get('name'));
						},
						'map': function(el, ref, item){
							var presetName = prompt('New preset name:');
							if (presetName != null){
								presetRequest('save', item.get('id'), item.get('name'), presetName);
							}							
						}
					}
		});
	});
});

//functions/classes

presetRequest = function(command, presetId, cameraId, name){
	var data = 'id='+cameraId+'&command='+command;
	if (command=='rename' || command=='save'){
		data += '&name='+name;
	};
	if (command!='save'){
		data += '&preset='+presetId;
	}
	var request = new Request.HTML({
		url: 'media/ptz',
		data: data,
		method: 'get',
		onRequest: function(){
		},
		onComplete: function(){
		},
		onFailure: function(){
		},
		onSuccess: function(tree, elements, html){
		}
	}).send();
}

addPtz = function(el, id){
	var ptzTable = new Element('div', {'class': 'ptzControls', 'id' : id, 'html' : '<div class="lu"></div><div class="nu"></div><div class="ru"></div><div class="ln"></div><div class=""></div><div class="rn"></div><div class="ld"></div><div class="nd"></div><div class="rd"></div><div class="t">+</div><div class="w">&ndash;</div><div class="presets" id="presetButton">p</div>'});
	ptzTable.inject(el.getParent(), 'bottom');
	ptzTable.getChildren().each(function(el){
		if (el.get('class')!='presets'){
			el.addEvents({
				'mousedown': function(ev){
					ev.stopPropagation();
					sendPtzCommand(this.getParent().get('id'), 'move', el.get('class'), true);
				},
				'mouseup': function(){
					var cmd = (el.get('class')=='t' || el.get('class') == 'w') ? 'stop_zoom' : 'stop'; 				
					sendPtzCommand(this.getParent().get('id'), cmd, el.get('class'), true);
				},
				'mouseout': function(){
					var cmd = (el.get('class')=='t' || el.get('class') == 'w') ? 'stop_zoom' : 'stop';
					sendPtzCommand(this.getParent().get('id'), cmd, el.get('class'), true);
				}
			});
		} else if ($('presets-'+id)==null) {
			el.setStyle('display', 'none');		
		}
	});
	
	
}

layoutsUpdate = function(mode, layout){
	var request = new Request({
		url: 'liveview/layouts',
		data: 'mode='+mode+'&layout='+layout,
		method: 'post',
		onRequest: function(){
		},
		onSuccess: function(text, xml){
			window.location.reload(true);
		},
		onFailure: function(){
		}
	}).send();
}

makeGrid = function(){
	
	var lvRows = (Cookie.read('lvRows') || 2);
	var lvCols = (Cookie.read('lvCols') || 2);
	var gridTable = new Element('table', {
           	'id' : 'lvGridTable'
    });
	for (row = 1; row <= lvRows; row++) {
       	var thisRow = new Element('tr', {'id' : row,'class' : 'y'+row});
       	for(col = 1; col <= lvCols; col++){
			var thisCol = new Element('td', {'id' : col, 'class' : 'x'+col});
			var imgSrcId = (Cookie.read('imgSrcy'+row+'x'+col) || 'none');
			var imgClass = 'noImg';
			var thisCam = $$('.ptz'+'#'+imgSrcId); 	var id = imgSrcId;
			// If assigned device, then m3u8 files will be displayed
			// Else a fixed image will be displayed to select camera
			var lvSource;
			if (imgSrcId === 'none') {
				lvSource = new Element('img', {'class': imgClass, 'src': '/img/icons/layouts/none.png'});
			} else {
				lvSource = new Element('video', {'id': 'video' + row + col, 'controls': '', 'autoplay': 'false', 'width': '100%', 'height': 'auto'});
			}
			lvSource.inject(thisCol);
			if (thisCam  && (thisCam.get('id')==id)) {
				addPtz(lvImg, id);
			}
            thisCol.inject(thisRow, 'bottom');
		};
  	    thisRow.inject(gridTable, 'bottom');
	};
    gridTable.inject($('liveViewContainer'));
	
	// Create video instance
	var videoSrcInHls = "https://devstreaming-cdn.apple.com/videos/streaming/examples/img_bipbop_adv_example_ts/master.m3u8";
	if(Hls.isSupported()) {
		for (row = 1; row <= lvRows; row++) {
			for(col = 1; col <= lvCols; col++){
				var video = document.getElementById("video" + row + col);
				var hls = new Hls();
				hls.loadSource(videoSrcInHls);
				hls.attachMedia(video);
				hls.on(Hls.Events.MANIFEST_PARSED,function() {
					video.play();
				});
			}
		}
	} else {
		//
	}
}

adjustImageSize = function(){
	//based on 704/480 aspect for ntsc

	var lvRows = (Cookie.read('lvRows') || 2);
	var lvCols = (Cookie.read('lvCols') || 2);
	var verticalAdj = 60;
	var horizontalAdj = 250;
	var maxHeight = 0;
	var maxWidth = 0;  
	if ((window.innerHeight-verticalAdj)/(lvRows*480) < (window.innerWidth-horizontalAdj)/(lvCols*704)){
		maxHeight = (window.innerHeight-verticalAdj)/(lvRows);
		maxWidth = 704*(maxHeight/480);
	} else {
		maxWidth = (window.innerWidth-horizontalAdj)/(lvCols);
		maxHeight = 480*(maxWidth/704);
	}
	
	$$('#liveViewContainer table tr td img').each(function(el){
		if ((el.width/el.height) > (704/480)){
			el.setStyle('width', maxWidth);
			el.setStyle('height', '');
		}
		else {
			el.setStyle('height', maxHeight);
			el.setStyle('width', '');
		}
	});
	window.addEvent('resize', function(){
		adjustImageSize();
	});
}
addDelRowColumn = function(n, t){
	var tp = ((n)=='c' ? 'lvCols' : 'lvRows');
	var v = parseInt(Cookie.read(tp) || 2);
	Cookie.write(tp, ((t) ? v+1 : v-1));
	window.location.reload(true);
},
	
setLayout = function(tp){
	Cookie.write('lvCols', Math.sqrt(tp)); Cookie.write('lvRows', Math.sqrt(tp));
	window.location.reload(true);
}


sendPtzCommand = function(camId, command, d, cont, speed){
	if (!speed) var speed = 32;
	var data = 'id='+camId+'&panspeed='+speed+'&tiltspeed='+speed;
	data += '&command='+command;
	if (command != "stop"){		
		data += (cont) ? '&duration=-1' : '&duration=250';
		if (d!='t' && d!='w'){
			if (d.substring(0,1)!='n') { data += '&pan='+d.substring(0,1); };
			if (d.substring(1,2)!='n') { data += '&tilt='+d.substring(1,2); };
		} else {
			data += '&zoom='+d;
		}
	}
	var request = new Request.HTML({
		url: 'media/ptz',
		data: data,
		method: 'get',
		onRequest: function(){
		},
		onComplete: function(){
		},
		onFailure: function(){
		},
		onSuccess: function(tree, elements, html){
		}
	}).send();
};


