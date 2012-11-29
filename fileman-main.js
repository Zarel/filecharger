/*
 *
 * File Manager
 * By Guangcong Luo - novawave.ca/gluo
 * Main Pane JavaScript File
 * 
 * CORE FILE - DO NOT DELETE
 *
 */


/*************************
 * Prepare
 *************************/

var nmo=true;
var cmo=false;
var t;


/*************************
 * Misc
 *************************/

function go(loc)
{
	parent.location.href = "index.php?d="+cdir+escape(loc).replace('+','%2B').replace('%20','+').replace('*','%2A')+asid;
}
function goand(loc,append)
{
	parent.location.href = "index.php?d="+cdir+escape(loc).replace('+','%2B').replace('%20','+').replace('*','%2A')+append+asid;
}
function jsesc(f) { return f.replace('\'','\\\''); }
function updatesb()
{
	var seld = getsels().join(',');
	if (seld.length == 0)
	{
		parent.sidebar.restore();
		parent.actbar.setmore('');
		return;
	}
	var sels = seld.split(',');
	var tmp = '';
	if (sels.length == 1)
	{
		tmp = tmp.concat('<div class="overflowable"><strong>',id2file(sels[0])).concat('</strong></div>');
		tmp = tmp.concat(filedesc[sels[0]]);
	}
	else
	{
		tmp = tmp.concat(sels.length);
		tmp = tmp.concat(' items selected.<br \/><br \/><div class="overflowable">');
		for (var i=0; i<5&&i<sels.length; i++)
		{
			if (i == 4) tmp = tmp.concat(', ...');
			else if (i == 0) tmp = tmp.concat(id2file(sels[i]));
			else tmp = tmp.concat(', ',parent.sidebar.id2file(sels[i]));
		}
		tmp = tmp.concat('</div>');
	}
	parent.sidebar.document.getElementById('details').innerHTML = tmp;
	var cmenu = ''; // It's amazing how well the context menu translates to the fftask.
	var mmenu = ''; // The More menu, unfortunately, does not.
	if (sels.length == 1)
	{
		var f=id2file(sels[0]);
		var ft=1;
		if (sels[0].substring(0,4)!='fold') ft=parent.sidebar.getft(f);
		if (ft==2||ft==3)
			cmenu = cmenu + '<div><a href="javascript:void(0);" onclick="parent.main.goand(\''+jsesc(f)+'\',\'&vmode=txt\')">View source</a></div>';
		if (ft==2||ft==3) cmenu = cmenu + '<div><a href="javascript:void(0);" onclick="parent.main.goand(\''+jsesc(f)+'\',\'&vmode=e_txt\')">Edit source</a></div>';
		if (ft==4) cmenu = cmenu + '<div><a href="javascript:void(0);" onclick="parent.main.goand(\''+jsesc(f)+'\',\'&vmode=e_txt\')">Edit</a></div>';
		if (ext_pclzip && ft==7) cmenu = cmenu + '<div><a href="javascript:void(0);" onclick="parent.sidebar.f_extract(\''+jsesc(f)+'\',0);">Extract here</a></div><div><a href="javascript:void(0);" onclick="parent.sidebar.f_extract(\''+jsesc(f)+'\',1);">Extract to <em>'+f.substr(0,(f.substr(f.length-4)=='.zip')?f.length-4:f.length)+'/</em></a></div><div><a href="javascript:void(0);" onclick="parent.sidebar.f_extract(\''+jsesc(f)+'\',2);">Extract to...</a></div>';
		if (ft!=1)
			cmenu = cmenu + '<div><a href="javascript:void(0);" onclick="parent.main.goand(\''+jsesc(f)+'\',\'&sp=dl\')">Download</a></div>';
		var tmp = '<div><a href="javascript:void(0);" onclick="f_rename(\''+jsesc(f)+'\')">Rename</a></div><div><a href="javascript:void(0);" onclick="parent.sidebar.f_delete(\''+seld+'\', false)">Delete</a></div>';
		if (ext_pclzip && ft==7)
			mmenu = '<div><a href="javascript:void(0)" onclick="drop(\'view\');return lopen(this)" id="l_view">Extract&nbsp;<span class="drop"><img src="images/dropdown.gif" /></span></a></div><div id="d_view" class="m">'+cmenu+'</div>';
		else if (ft!=1)
			mmenu = '<div class="nopen" id="l_view"><table border="0" cellspacing="0" cellpadding="0"><tr><td valign="top"><a class="l" href="javascript:void(0);" onclick="parent.main.go(\''+jsesc(f)+'\')">View</a></td><td valign="top"><a class="drop" href="javascript:void(0);" onclick="drop(\'view\');return lopen(document.getElementById(\'l_view\'));">&nbsp;<img src="images/dropdown.gif" /></a><div id="d_view" class="m">'+cmenu+'</div></td></tr></table></div>';
		else
			mmenu = '<div><a href="javascript:void(0);" onclick="parent.main.go(\''+jsesc(f)+'\')">Open</a></div>';
		mmenu = mmenu+tmp+'<div><a href="javascript:void(0)" onclick="drop(\'more\');return lopen(this)" id="l_more">More&nbsp;<span class="drop"><img src="images/dropdown.gif" /></span></a></div><div id="d_more" class="m">'+(cliptype==''?'<div><a href="javascript:void(0);" onclick="f_clip(\'c\')">Copy to...</a></div><div><a href="javascript:void(0);" onclick="f_clip(\'m\')">Move to...</a></div>':'<div><a href="javascript:void(0);" onclick="f_clip(\''+cliptype+'\')">Add to '+(cliptype=='c'?'copy':'move')+'</a></div>')+(ext_pclzip?'<div><a href="javascript:void(0);" onclick="parent.main.f_compress()">Make into zip...</a></div>':'')+(ft==0||ft>4?'<div class="hr"><hr></div><div><a href="javascript:void(0);" onclick="goand(\''+jsesc(f)+'\',\'&vmode=txt\')">View as text</a></div><div><a href="javascript:void(0);" onclick="goand(\''+jsesc(f)+'\',\'&vmode=e_txt\')">Edit as text</a></div><div><a href="javascript:void(0);" onclick="goand(\''+jsesc(f)+'\',\'&vmode=frame\')">View in-browser</a></div>':'')+'<div class="hr"><hr></div><div><a href="javascript:void(0);" onclick="goand(\''+jsesc(f)+'\',\'&vmode=props\')">Properties</a></div></div>';
		cmenu = '<div><a href="javascript:void(0);" onclick="parent.main.go(\''+jsesc(f)+'\')">'+(ft==1?'Open':'View')+'</a></div>'+cmenu;
		cmenu = cmenu+tmp;
	}
	else
		mmenu = (cmenu = '<div><a href="javascript:void(0);" onclick="parent.sidebar.f_delete(\''+seld+'\', false)">Delete files</a></div>')+'<div><a href="javascript:void(0)" onclick="drop(\'more\');return lopen(this)" id="l_more">More&nbsp;<span class="drop"><img src="images/dropdown.gif" /></span></a></div><div id="d_more" class="m">'+(cliptype==''?'<div><a href="javascript:void(0);" onclick="f_clip(\'c\')">Copy to...</a></div><div><a href="javascript:void(0);" onclick="f_clip(\'m\')">Move to...</a></div>':'<div><a href="javascript:void(0);" onclick="f_clip(\''+cliptype+'\')">Add to '+(cliptype=='c'?'copy':'move')+'</a></div>')+(ext_pclzip?'<div><a href="javascript:void(0);" onclick="parent.main.f_compress()">Make into zip...</a></div>':'')+'</div>';
	parent.sidebar.document.getElementById('fftask').innerHTML = cmenu;
	parent.actbar.setmore(mmenu);
}

/*************************
 * Selection
 *************************/

function sel(id)
{
	togsel(id);
	updatesb();
}
function togsel(id) // Toggle sel without updatesb
{
	if (document.getElementById(id).className == 'sel')
		document.getElementById(id).className = 'nsel';
	else if (document.getElementById(id).className == 'nsel')
		document.getElementById(id).className = 'sel';
	else if (document.getElementById(id).className == 'sel_hover')
		document.getElementById(id).className = 'nsel_hover';
	else if (document.getElementById(id).className == 'nsel_hover')
		document.getElementById(id).className = 'sel_hover';
}
function issel(id) // Is selected?
{
	return document.getElementById(id).className == 'sel'
	|| document.getElementById(id).className == 'sel_hover';
}
function selonly(id)
{
	if (issel(id)) togsel(id);
	else
	{
		var seld = getsels();
		if (seld.length != 0)
		{
			for (var i=0;i<seld.length;i++)
			if (issel(seld[i])) togsel(seld[i]);
		}
		if (!issel(id)) togsel(id);
	}
	updatesb();
}
function selall()
{
	var elems = document.getElementsByTagName('li');
	for (var i=0;i<elems.length;i++)
		if (!issel(elems[i].id.substr(3)))
			togsel(elems[i].id.substr(3));
	updatesb();
}
function selinv()
{
	var elems = document.getElementsByTagName('li');
	for (var i=0;i<elems.length;i++)
		togsel(elems[i].id.substr(3));
	updatesb();
}
function deselall()
{
	var seld = getsels();
	for (var i=0;i<seld.length;i++)
		togsel(seld[i]);
	updatesb();
}
function getsels()
{
	var sels = new Array();
	var elems = document.getElementsByTagName('li');
	for (var i=0;i<elems.length;i++)
	{
		if (issel(elems[i].id.substr(3)))
			sels.push(elems[i].id.substr(3));
	}
	return sels;
}
function exists(id)
{
	if (document.getElementById(id)) return true; return false;
}
function fclick(e, id)
{
	if (!e) var e = window.event;
	if ((e.which && e.which == 3) || (e.button && e.button == 2))
		return;
	e.cancelBubble = true;
	if (e.stopPropagation) e.stopPropagation();
	if (!e.ctrlKey&&!e.shiftKey) selonly(id);
	else sel(id);
}


/*************************
 * Context menu
 *************************/

function cmenu(e, id, f, ft)
{
	if (!loaded) return false;
	if (!issel(id)) selonly(id);
	var ie5=document.all&&document.getElementById;
	var redge=ie5? document.body.clientWidth-event.clientX : window.innerWidth-e.clientX;
	var bedge=ie5? document.body.clientHeight-event.clientY : window.innerHeight-e.clientY;
	var cmenu = '';
	
	mdown = false;

	cmenu = '<div><a href="javascript:void(0);" onclick="go(\''+jsesc(f)+'\')"><strong>'+(ft==1?'Open':'View')+'</strong></a></div>';
	if (ft==2||ft==3) cmenu = cmenu + '<div><a href="javascript:void(0);" onclick="goand(\''+jsesc(f)+'\',\'&vmode=txt\')">View source</a></div>';
	if (ft==2||ft==3) cmenu = cmenu + '<div><a href="javascript:void(0);" onclick="goand(\''+jsesc(f)+'\',\'&vmode=e_txt\')">Edit source</a></div>';
	if (ft==4) cmenu = cmenu + '<div><a href="javascript:void(0);" onclick="goand(\''+jsesc(f)+'\',\'&vmode=e_txt\')">Edit</a></div>';
		if (ext_pclzip && ft==7) cmenu = cmenu + '<div><a href="javascript:void(0);" onclick="parent.sidebar.f_extract(\''+jsesc(f)+'\',0);">Extract here</a></div><div><a href="javascript:void(0);" onclick="parent.sidebar.f_extract(\''+jsesc(f)+'\',1);">Extract to <em>'+f.substr(0,(f.substr(f.length-4)=='.zip')?f.length-4:f.length)+'/</em></a></div><div><a href="javascript:void(0);" onclick="parent.sidebar.f_extract(\''+jsesc(f)+'\',2);">Extract to...</a></div>';
	if (ft!=1) cmenu = cmenu + '<div><a href="javascript:void(0);" onclick="goand(\''+jsesc(f)+'\',\'&sp=dl\')">Download</a></div>';
	cmenu = cmenu + '<div class="hr"><hr></div>';
	if (cliptype=='')
		cmenu = cmenu + '<div><a href="javascript:void(0);" onclick="f_clip(\'c\')">Copy to...</a></div><div><a href="javascript:void(0);" onclick="f_clip(\'m\')">Move to...</a></div>';
	else
		cmenu = cmenu + '<div><a href="javascript:void(0);" onclick="f_clip(\''+cliptype+'\')">Add to '+(cliptype=='c'?'copy':'move')+'</a></div>';
	cmenu = cmenu + '<div class="hr"><hr></div>';
	cmenu = cmenu + '<div><a href="javascript:void(0);" onclick="f_rename(\''+jsesc(f)+'\')">Rename</a></div><div><a href="javascript:void(0);" onclick="f_delete()">Delete</a></div><div class="hr"><hr></div><div><a href="javascript:void(0);" onclick="goand(\''+jsesc(f)+'\',\'&vmode=props\')">Properties</a></div>';
	document.getElementById('cmenu').innerHTML = cmenu;

	if (redge<document.getElementById('cmenu').offsetWidth)
		document.getElementById('cmenu').style.left = '' + (ie5?(document.body.scrollLeft+event.clientX-document.getElementById('cmenu').offsetWidth):(window.pageXOffset+e.clientX-document.getElementById('cmenu').offsetWidth)) + 'px';
	else
		document.getElementById('cmenu').style.left = '' + (ie5?(document.body.scrollLeft+event.clientX):(window.pageXOffset+e.clientX)) + 'px';
	if (bedge<document.getElementById('cmenu').offsetHeight)
		document.getElementById('cmenu').style.top = '' + (ie5?(document.body.scrollTop+event.clientY-document.getElementById('cmenu').offsetHeight):(window.pageYOffset+e.clientY-document.getElementById('cmenu').offsetHeight)) + 'px';
	else
		document.getElementById('cmenu').style.top = '' + (ie5?(document.body.scrollTop+event.clientY):(window.pageYOffset+e.clientY)) + 'px';

	document.getElementById('cmenu').style.display="block";
	cmo=true;cmmo=false;return false;
}
function cmenu_c(x,y,cmenu)
{
        y = (y || document.body.scrollTop || document.documentElement.scrollTop);
	if (!loaded) return false;
	if (cmenu == 'N')
		cmenu = '<div><a href="javascript:void(0);" onclick="parent.sidebar.f_newfold();">New Folder</a></div><div><a href="javascript:void(0);" onclick="parent.sidebar.f_newfile();">New Text Document</a></div><div><a href="javascript:void(0);" onclick="parent.sidebar.f_urlupload();">Upload from URL</a></div>';

	document.getElementById('cmenu').innerHTML = cmenu;

	document.getElementById('cmenu').style.left = '' + (x) + 'px';
	document.getElementById('cmenu').style.top = '' + (y) + 'px';

	document.getElementById('cmenu').style.display = 'block';
	cmo=true;return false;
}
function cmenu_nmo(e)
{
	if (!nmo || !loaded) return false;
	//if (document.getElementById(id).className == 'nsel') selonly(id);
	var ie5=document.all&&document.getElementById;
	var redge=ie5? document.body.clientWidth-event.clientX : window.innerWidth-e.clientX;
	var bedge=ie5? document.body.clientHeight-event.clientY : window.innerHeight-e.clientY;

	mdown = false;

	var cmenu = '<div><a href="javascript:void(0);" onclick="parent.sidebar.f_newfold();">New Folder</a></div><div><a href="javascript:void(0);" onclick="parent.sidebar.f_newfile();">New Text Document</a></div><div><a href="javascript:void(0);" onclick="parent.main.fdrag_openform();">Upload</a></div><div><a href="javascript:void(0);" onclick="parent.sidebar.f_urlupload();">Upload from URL</a></div>'+(cliptype==''?'':'<div class="hr"><hr></div><div><a href="javascript:void(0);" onclick="f_pastehere(); return false">Paste here</a></div>')+'<div class="hr"><hr></div><div><a href="javascript:void(0);" onclick="selall()">Select All</a></div><div><a href="javascript:void(0);" onclick="selinv()">Invert selection</a></div>';
	document.getElementById('cmenu').innerHTML = cmenu;

	if (redge<document.getElementById('cmenu').offsetWidth)
		document.getElementById('cmenu').style.left = '' + (ie5?(document.body.scrollLeft+event.clientX-document.getElementById('cmenu').offsetWidth):(window.pageXOffset+e.clientX-document.getElementById('cmenu').offsetWidth)) + 'px';
	else
		document.getElementById('cmenu').style.left = '' + (ie5?(document.body.scrollLeft+event.clientX):(window.pageXOffset+e.clientX)) + 'px';
	if (bedge<document.getElementById('cmenu').offsetHeight)
		document.getElementById('cmenu').style.top = '' + (ie5?(document.body.scrollTop+event.clientY-document.getElementById('cmenu').offsetHeight):(window.pageYOffset+e.clientY-document.getElementById('cmenu').offsetHeight)) + 'px';
	else
		document.getElementById('cmenu').style.top = '' + (ie5?(document.body.scrollTop+event.clientY):(window.pageYOffset+e.clientY)) + 'px';

	document.getElementById('cmenu').style.display="block";
	cmo=true;cmmo=false;return false;
}
function cmenucl()
{
	if (!cmo || !loaded) return;
	cmo=false;
	document.getElementById('cmenu').style.display = 'none';
	document.getElementById('cmenu').innerHTML = '&nbsp;';
	parent.actbar.lclose();
}

// Selection box

var selbx = 0, selby = 0, selbx2 = 0, selby2 = 0;
var selb = false; // selection box
var cmmo = false, selbctrl = false;
var mdown = false;

function mousedown(e)
{
	if (!e) var e = window.event;
	if ((e.which && e.which == 3) || (e.button && e.button == 2))
		return true;
	if (!loaded) return false;
	mdown = true;
	//if (cmo) { cmenucl(); }
	if (selb) mouseup();
	else if (document.getElementsByTagName('li').length != 0)
	{
		e = e || window.event;
		var ie5=document.all&&document.getElementById;
		selbx2 = selbx = (ie5?(document.body.scrollLeft+event.clientX):(window.pageXOffset+e.clientX));
		selby2 = selby = (ie5?(document.body.scrollTop+event.clientY):(window.pageYOffset+e.clientY));

		window.onmousemove = mousemoveinit;
		selbctrl = (e.ctrlKey || e.shiftKey || e.metaKey || e.cmdKey);
	}
	return false;
}
function mousemoveinit(e)
{
	var ie5=document.all&&document.getElementById;
	selbx2 = (ie5?(document.body.scrollLeft+event.clientX):(window.pageXOffset+e.clientX));
	selby2 = (ie5?(document.body.scrollTop+event.clientY):(window.pageYOffset+e.clientY));
	if (selbx2==selbx && selby2==selby)
		return false; // Workaround for Safari/Chrome bug
	cmenucl();
	mdown = false;
	if (!selbctrl) deselall();
	window.onmousemove = mousemove; selb = true;
	document.getElementById('selbox').style.display = 'block';
	document.getElementById('selbox').style.top = ''+(selby-1)+'px';
	document.getElementById('selbox').style.left = ''+(selbx-1)+'px';
	document.getElementById('selbox').style.width = '1px';
	document.getElementById('selbox').style.height = '1px';
	document.getElementById('dialog').innerHTML = '';
	document.getElementById('dialog').style.display = 'block';
	document.getElementById('dialog').className = 'nobg';
	return false;
}
function mousemove(e)
{
	var ie5=document.all&&document.getElementById;
	selbx2 = (ie5?(document.body.scrollLeft+event.clientX):(window.pageXOffset+e.clientX));
	selby2 = (ie5?(document.body.scrollTop+event.clientY):(window.pageYOffset+e.clientY));
	document.getElementById('selbox').style.top = ''+(min(selby,selby2)-1)+'px';
	document.getElementById('selbox').style.left = ''+(min(selbx,selbx2)-1)+'px';
	document.getElementById('selbox').style.width = ''+(adiff(selbx,selbx2)+1)+'px';
	document.getElementById('selbox').style.height = ''+(adiff(selby,selby2)+1)+'px';
	return false;
}
function mouseup(e)
{
	if (!loaded) return false;
	window.onmousemove = null;
	document.getElementById('selbox').style.display = 'none';
	document.getElementById('dialog').style.display = 'none';
	if (!selb)
	{
		if (cmo && !cmmo && mdown) cmenucl(); else if (!cmo && nmo) deselall(); return false;
	}
	selb = false;
	mdown = false;

	var elems = document.getElementsByTagName('li');
	var x0 = xpos(elems[0]), y0 = ypos(elems[0]), dx = 76, dy = 58, w = elems.length, tmpy = 10;
	for (var i=0; i<elems.length; i++) if ((tmpy=ypos(elems[i]))!= y0)
	{
		w = i; dy = tmpy-y0; break;
	}
	if (elems.length>0) dx = xpos(elems[1])-x0;

	//if (dx != 76 || dy != 58)
	//alert('76 != '+dx+' || 58 != '+dy);

	var fc = max(Math.floor((min(selbx,selbx2)-x0+5)/dx),0);
	var lc = min(Math.floor((max(selbx,selbx2)-x0-5)/dx),w-1);
	var fr = max(Math.floor((min(selby,selby2)-y0+5)/dy),0);
	var lr = Math.floor((max(selby,selby2)-y0-5)/dy);
	if (fc>lc || fr>lr) return false;

	for (var i=fr; i<=lr; i++)
	{
		for (var j=fc; j<=lc; j++)
		{
			if (i*w+j>=elems.length) break;
			if (!issel(elems[i*w+j].id.substr(3))) togsel(elems[i*w+j].id.substr(3));
		}
	}
	updatesb();

	return false;
}

function min(a,b)
{
	return a>b?b:a;
}
function max(a,b)
{
	return a>b?a:b;
}
function adiff(a,b)
{
	return a>b?(a-b):(b-a);
}

function xpos(obj)
{
	var x = 0;
	if (obj.offsetParent) do
		x += obj.offsetLeft;
	while (obj = obj.offsetParent);
	return x;
}
function ypos(obj)
{
	var y = 0;
	if (obj.offsetParent) do
		y += obj.offsetTop;
	while (obj = obj.offsetParent);
	return y;
}

/*upload*/

function fdrag_canupload() {
	return (typeof(FileReader) != "undefined" || typeof(FormData) != "undefined");
}
function fdrag_upload(event) {
    var files = event;
    if (typeof event.dataTransfer != "undefined")
    {
    	files = event.dataTransfer.files;
	    /* Prevent browser opening the dragged file. */
	    event.stopPropagation();
	    event.preventDefault();
    }

	if (typeof(FileReader) == "undefined" && typeof(FormData) == "undefined")
	{
		alert('Error: Your browser doesn\'t support drag-and-drop file uploading. Please try Firefox or Chrome.');
		return;
	}
    
    xhr = new XMLHttpRequest();
    
    xhr.open("POST", "index.php?response=ajax&d="+cdir+asid, true);
    xhr.upload.addEventListener('progress', fdrag_onprogress, false); // progress bar
    
    xhr.onload = function(event) {
        /* If we got an error display it. */
        if (xhr.responseText.substr(0,8) == 'success;') {
        	location.replace(xhr.responseText.substr(8));
        }
        else {
            fdrag_doneuploading();
            document.getElementById('filelist').innerHTML = '<p><strong>An error occurred while uploading your file.</strong> '+xhr.responseText+'</p><button onclick="location.replace(\'index.php?d='+cdir+'&p=v'+asid+'\')">Ok</button>';
        }
    };
    
	if (typeof(FormData) == "undefined")
    {
	    // Firefox 3.6
	    var boundary = '------multipartformboundary' + (new Date).getTime();
	    var dashdash = '--';
	    var crlf     = '\r\n';
	
	    /* Build RFC2388 string. */
	    var builder = '';
	    builder += dashdash;
	    builder += boundary;
	    builder += crlf;
        xhr.setRequestHeader('content-type', 'multipart/form-data; boundary=' 
        + boundary);
	    /* For each dropped file. */
	    for (var i = 0; i < files.length; i++) {
	        var file = files[i];
	
	        /* Generate headers. */            
	        builder += 'Content-Disposition: form-data; name="dragupload[]"';
	        if (file.fileName) {
	          builder += '; filename="' + file.fileName + '"';
	        }
	        builder += crlf;
	
	        builder += 'Content-Type: application/octet-stream';
	        builder += crlf;
	        builder += crlf; 
	
	        /* Append binary data. */
	        builder += file.getAsBinary();
	        //var reader = new FileReader();
	        //reader.readAsBinaryString(file);
	        //builder += reader.result;
	        
	        builder += crlf;
	
	        /* Write boundary. */
	        builder += dashdash;
	        builder += boundary;
	        builder += crlf;
	    }
	    
	    /* Mark end of the request. */
	    builder += dashdash;
	    builder += boundary;
	    builder += dashdash;
	    builder += crlf;
	
	    xhr.sendAsBinary(builder);
    }
    else
    {
    	var formdata = new FormData();
		for (var i = 0; i < files.length; i++) {
			formdata.append("dragupload[]",files[i]);
		}
    	xhr.send(formdata);
    }
    fdrag_uploading();
}

/*************************
 * File manipulation
 *************************/

function f_rename(f)
{
	parent.sidebar.f_rename(f);
}
function f_delete()
{
	parent.sidebar.f_delete(getsels().join(','), false);
}
function f_compress()
{	parent.sidebar.f_compress(getsels().join(',')) }
function f_clip(t)
{	parent.sidebar.f_clip(t,getsels().join(',')) }
function f_declip()
{	parent.sidebar.f_declip(getsels().join(',')) }
function f_declipall()
{	parent.sidebar.f_declipall() }
function f_pastehere()
{	parent.sidebar.f_pastehere() }
function isclipped(id)
{	return document.getElementById('li_'+id).className=='clipped'; }
function id2file(id)
{	return document.getElementById(id).title+(id.substring(0,4)=='fold'?'/':''); }
function keypress(e)
{
	e = e || window.event;
	if ((e.ctrlKey || e.metaKey || e.cmdKey) && (
	e.keyCode==65 || e.keyCode==88 || e.keyCode==67 || e.keyCode==86
	)) return false;
}
function keydown(e)
{
	e = e || window.event;
	if (e.keyCode == 17 || e.keyCode == 16 || e.keyCode == 18) return;
	var usingmac = (navigator.appVersion.indexOf("Mac")!=-1);
	//alert('Key pressed; keyCode='+e.keyCode+' charCode='+e.charCode+' modifiers='+e.modifiers);
	if ((e.ctrlKey || e.metaKey || e.cmdKey) && e.keyCode==65) // Ctrl+A
	{
		cmenucl(); selall();
		return false;
	}
	if ((e.ctrlKey || e.metaKey || e.cmdKey) && e.keyCode==88) // Ctrl+X
	{
		cmenucl(); if (cliptype=='c') { alert('Error: Clipboard not empty'); return false; }
		f_clip('m');
		return false;
	}
	if ((e.ctrlKey || e.metaKey || e.cmdKey) && e.keyCode==67) // Ctrl+C
	{
		cmenucl(); if (cliptype=='m') { alert('Error: Clipboard not empty'); return false; }
		f_clip('c');
		return false;
	}
	if ((e.ctrlKey || e.metaKey || e.cmdKey) && e.keyCode==86) // Ctrl+V
	{
		cmenucl(); f_pastehere();
		return false;
	}
	if ((e.ctrlKey || e.metaKey || e.cmdKey) && (e.keyCode==46 || (e.keyCode==8 && usingmac))) // Ctrl+Del
	{
		cmenucl(); parent.sidebar.f_delete(getsels().join(','), true);
		return false;
	}
	if (e.keyCode==46 || (e.keyCode==8 && usingmac)) // Del
	{
		cmenucl(); f_delete();
		return false;
	}
	if (e.keyCode==113 || (e.keyCode==13 && usingmac)) // F2 (Enter on Mac)
	{
		cmenucl(); var sels = getsels();
		if (sels.length>0) f_rename(id2file(sels[0]));
		return false
	}
	if ((e.keyCode==37 || e.keyCode==39 || e.keyCode==38 || e.keyCode==40) &&
	    !e.metaKey && !e.ctrlKey && !e.altKey && !e.cmdKey) // <-
	{
		cmenucl(); var selected = -1, oldsel;
		var elems = document.getElementsByTagName('li');
		if (!elems.length) return false;
		for (var i=0;i<elems.length;i++)
		{
			if (issel(elems[i].id.substr(3)))
			{
				if (selected == -1) selected = i;
				else return false;
			}
		}
		if (selected == -1)
		{
			selonly(elems[0].id.substr(3));
			return false;
		}
		oldsel = selected;

		if (e.keyCode == 37) selected--; // left
		else if (e.keyCode == 39) selected++; // right
		else
		{
			var x0 = xpos(elems[0]), y0 = ypos(elems[0]), dx = 76, dy = 58, w = elems.length, tmpy = 10;
			for (var i=0; i<elems.length; i++) if ((tmpy=ypos(elems[i]))!= y0)
			{
				w = i; break;
			}
			if (e.keyCode == 38) selected -= w; // up
			else if (e.keyCode == 40) selected += w; // down
		}
		if (selected < 0) selected = 0;
		if (selected >= elems.length) selected = elems.length-1;
		if (selected != oldsel) selonly(elems[selected].id.substr(3));
		return false;
	}
	if ((e.keyCode==13 || ((e.metaKey || e.cmdKey || e.altKey) && e.keyCode == 40)) && !cmo)
	{
		var sels = getsels();
		if (sels.length != 1) return;
		var f=id2file(sels[0]);
		go(f);
	}
}
function keyup(e)
{}
function resize()
{}