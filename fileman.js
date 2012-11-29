/*
 *
 * Filecharger
 * by Zarel of Novawave
 * JavaScript File
 * 
 * CORE FILE - DO NOT DELETE
 *
 */

var fm_version = '0.0.8 Alpha';

/*************************
 * Preloading
 *************************/

var pw = 0;
var ph = 0;
var tbh = 0;
var sbw = 0; 
var shbh = 89; // Shelf bar height
var state = 'l';
var d = ''; var p = ''; var s = ''; var u = '';

window.onload = preload_page;
window.onresize = resize_page;

function preload_page()
{
  tbh = document.getElementById('topbar').offsetHeight;
  sbw = document.getElementById('sidebar').offsetWidth;
  resize_page();
  
  /***[ Disable selection ]***/
  //document.getElementById('content').onmousedown = justsayno;
  //document.getElementById('content').onselectstart = justsayno;
  
  init();
}

function init()
{
  u = '';
  document.getElementById('fft_login').href='javascript:login();';
  document.getElementById('fft_login').innerHTML='Log in';
  login();
}
function extr_init(user,pass)
{
  document.getElementById('dir').innerHTML = '[Loading]';
  document.getElementById('content').innerHTML = '<div class="cmsg"><strong class="loading">Loading...</strong></div>'
  ajax_init(user,pass);
}
function login()
{
  fm_chgstate('l');
  document.getElementById('fft_cf').style.display = 'none';
  document.getElementById('dir').innerHTML = '[Waiting]';
  //document.getElementById('content').innerHTML = '<div class="cmsg"><strong class="loading">Waiting for input...</strong></div>';
  document.getElementById('content').innerHTML = '<div class="cmsg"><h1>Log in</h1><p>Input your username and password, or leave as it is to log in as guest.</p><form action="fileman.html" onsubmit="extr_init(document.getElementById(\'extv_un\').value,document.getElementById(\'extv_pw\').value);return false"><table border="0" cellspacing="1" cellpadding="0"><tr><td>Username:</td><td><input type="text" id="extv_un" value="guest" onkeyup="if (document.getElementById(\'extv_un\').value == \'guest\' || document.getElementById(\'extv_un\').value == \'\') document.getElementById(\'extv_pw\').disabled = true; else document.getElementById(\'extv_pw\').disabled = false;" /></td></tr><tr><td>Password:</td><td><input type="password" id="extv_pw" value="" disabled="disabled" /></td></tr></table><p><input type="submit" value="Log In" /> <input type="button" value="Cancel" onclick="extr_init(\'\',\'\');" /></p></form></div>';
}

function resize_page()
{
  pw = document.getElementById('contentwrapper').offsetWidth;
  ph = document.getElementById('contentwrapper').offsetHeight;
  document.getElementById('sidebar').style.height = ''+(ph-tbh)+'px';
  document.getElementById('sidebar_r').style.height = ''+(ph-tbh)+'px';
  if (state == 'l')
    document.getElementById('content').style.height = ''+(ph-tbh)+'px';
  else if (state == 'f')
    document.getElementById('flist').style.height = ''+(ph-tbh-shbh)+'px';
//  document.getElementById('exttable').style.height = ''+(ph-tbh)+'px';
//  document.getElementById('exttable').style.width = ''+(pw-sbw)+'px';
}
function resize_sidebar()
{
  document.getElementById('sidebar').style.width = ''+(sbw-3)+'px';
  document.getElementById('sidebar_r').style.left = ''+(sbw-3)+'px';
  document.getElementById('cpositioner').style.paddingLeft = ''+sbw+'px';
  document.getElementById('slist').style.left = ''+sbw+'px';
}
var sbr_mnd = true;
function sbr_md()
{
  sbr_mnd = false;
}
function sbr_mu()
{
  sbr_mnd = true;
}
function sbr_mm(e)
{
  if (sbr_mnd) return;
  var ie5=document.all&&document.getElementById;
  sbw = (ie5?(event.clientX):(e.clientX));
  resize_sidebar();
}

function justsayno() /* GET IT? AHAHAHAHAHA... I know, not funny */
{ return false; }

/*************************
 * Main
 *************************/

function fm_chgstate(newState)
{
  if (newState == state) return;
  if (state == 'l')
    document.getElementById('content').style.display='none';
  else if (state == 'f')
  {
    document.getElementById('shelfbar').style.display='none';
    document.getElementById('flist').style.display='none';
  }
  state = newState;
  if (state == 'l')
    document.getElementById('content').style.display='block';
  else if (state == 'f')
  {
    document.getElementById('shelfbar').style.display='block';
    document.getElementById('flist').style.display='block';
  }
  resize_page();
}

function fm_loaderr(err)
{
  fm_chgstate('l');
  //ext_alert('Error',err,'');
  document.getElementById('dir').innerHTML = '[Error]';
  document.getElementById('content').innerHTML='<div class="cmsg"><strong class="loading">Error: '+err+' <br /><input type="button" value="Try again?" onclick="init();" /></strong></div>';
}

function fm_elem(i)
{
  if (i>=0) id='f'+i;
  else id='c'+(-1-i);
  switch (fl_type)
  {
  case 0:
    return '<a id="'+id+'" class="n" onmouseover="mi(\''+id+'\')" onmouseout="mo(\''+id+'\')" onmousedown="md(event,\''+id+'\')" onmouseup="mu(\''+id+'\')" ondblclick="m2c(\''+id+'\')" oncontextmenu="mcm(\''+id+'\')"><span class="sdi"><img src="images/icons32/'+fli(i,'i')+'.gif" width="32" height="32" /></span><span class="sd">'+fli(i,'n')+'</span></a>';
  default:
    return 'Error.';
  }
}

function fm_melem(i)
{
  return '<div class="mof"><img src="images/icons/'+fli(i,'i')+'.gif" width="16" height="16" /> '+fli(i,'n')+'</div>';
}

function id2i(id)
{ return (id.substring(0,1)=='f') ? parseInt(id.substring(1)) : -1-parseInt(id.substring(1)); }

function fm_pageerr()
{
  if (document.getElementById('dir').innerHTML == '[Loading]')
  {
    fm_loaderr('There was an unidentified JavaScript error with the page.');
    return false;
  }
  return true;
}

function fm_thumb(i,size)
{
  return '';
}


/*************************
 * File List
 *************************/

var moid = '';

function getsels()
{
  var sels = new Array();
  for (var i=0;i<fl_c;i++)
  {
    if (document.getElementById('f'+i).className == 'sel')
      sels.push('f'+i);
  }
  return sels;
}
function updatesels(sels)
{
  var sels = getsels();
  if (sels.length == 0)
  {
    document.getElementById('details').innerHTML = '<div class="overflowable"><strong>'+fci('n')+'</strong></div>'+fci('t')+'<br /><br />Modified: '+fci('tm')+'<br />Size: '+fci('ts');
    document.getElementById('fft_cf').innerHTML = '<div id="fft_cf" style="display:none;"><div><a href="javascript:file_newdir();">New folder</a></div><div><a href="javascript:file_newfile();">New file</a></div><div><a href="javascript:file_upload();">Upload file</a></div><div>&nbsp;</div>';
  }
  else if (sels.length == 1)
  {
    var i = id2i(sels[0]);
    document.getElementById('details').innerHTML = '<div class="overflowable"><strong>'+fli(i,'n')+'</strong></div>'+(fli(i,'vi')==''?'':fm_thumb(i,250)+'<br />Dimensions: '+fliis(i,'w')+'x'+fliis(i,'h')+'<br /><br />')+fli(i,'t')+'<br /><br />Modified: '+fli(i,'tm')+'<br />Size: '+fli(i,'ts');
    document.getElementById('fft_cf').innerHTML = '<div>&nbsp;</div>';
  }
  else
  {
    var tsize = 0;
    for (var i=0;i<sels.length;i++)
      tsize += parseInt(fli(id2i(sels[i]),'s'));
    document.getElementById('details').innerHTML = ''+sels.length+' items selected.<br /><br />Total size: '+tfs(tsize);
    document.getElementById('fft_cf').innerHTML = '<div><a href="javascript:file_delete();">Delete files</a></div><div>&nbsp;</div>';
  }
}
function tfs(fs) // Copied from Fileman lib.
{
  if (fs >= 1048576)
    return ''+(Math.round(fs/10485.76)/100.0)+' MB';
  if( fs >= 1024 )
    return ''+(Math.round(fs/10.24)/100.0)+' KB';
  if( fs != 1 )
    return ''+fs+' bytes';
  return '1 byte';
}
function sel(id)
{
  if (document.getElementById(id).className == 'h')
    document.getElementById(id).className = 'hsel';
  else if (document.getElementById(id).className == 'n')
    document.getElementById(id).className = 'sel';
  updatesels();
}
function sel_nu(id)
{
  if (document.getElementById(id).className == 'h')
    document.getElementById(id).className = 'hsel';
  else if (document.getElementById(id).className == 'n')
    document.getElementById(id).className = 'sel';
}
function desel(id)
{
  if (document.getElementById(id).className == 'hsel')
    document.getElementById(id).className = 'h';
  else if (document.getElementById(id).className == 'sel')
    document.getElementById(id).className = 'n';
  updatesels();
}
function desel_nu(id)
{
  if (document.getElementById(id).className == 'hsel')
    document.getElementById(id).className = 'h';
  else if (document.getElementById(id).className == 'sel')
    document.getElementById(id).className = 'n';
}
function deselall()
{
  var sels = getsels();
  if (sels.length == 0) return;
  for (var i=0;i<sels.length;i++)
    desel_nu(sels[i]);
  updatesels();
}
function deselall_nu()
{
  var sels = getsels();
  for (var i=0;i<sels.length;i++)
    desel_nu(sels[i]);
}
function selall()
{
  for (var i=0;i<fl_c;i++)
    sel_nu('f'+i);
  updatesels();
}
function selinv()
{
  for (var i=0;i<fl_c;i++)
  {
    if (issel('f'+i))
      desel('f'+i);
    else
      sel('f'+i);
  }
  updatesels();
}
function issel(id)
{
  return (document.getElementById(id).className == 'sel' || document.getElementById(id).className == 'hsel');
}

function mi(id) /* Mouse over (think mouse-in) */
{
  moid = id;
  if (document.getElementById(id).className == 'n')
    document.getElementById(id).className = 'h';
  else if (document.getElementById(id).className == 'sel')
    document.getElementById(id).className = 'hsel';
}
function mo(id) /* Mouse out */
{
  moid = '';
  if (document.getElementById(id).className=='h')
    document.getElementById(id).className = 'n';
  else if (document.getElementById(id).className == 'hsel')
    document.getElementById(id).className = 'sel';
}
function md(e,id) /* Mouse down */
{
  moid = id;
  if (id.charAt(0)=='s')
  {
  }
  else
  {
    e.cancelBubble = true;
    var sels = getsels();
    if (sels.length==1 && issel(id))
      desel(id);
    else if (e.ctrlKey || e.shiftKey)
    {
      if (issel(id)) desel(id);
      else sel(id);
    }
    else
    {
      deselall_nu();
      sel(id);
    }
  }
}
function mu(id) /* Mouse up */
{}
function m2c(id) /* Mouse double-click */
{
  var i=0;
  if (id == 'su')
  {
    if (document.getElementById('su').className != 'd') ajax_rq(d+'../');
  }
  else if (id == 'sn')
  {}
  else if (id == 'st')
  {}
  else
  {
    i = id2i(id);
    switch (parseInt(fli(i,'ft')))
    {
    case 1:
      ajax_rq(d+fli(i,'n'));
      return;
    case 2:
    case 3:
      file_view(d+fli(i,'n'));
      return;
    case 4:
    case 5:
    case 6:
      file_open('preview',d+fli(i,'n'));
      return;
    default:
      ext_alert('Error','Unknown file type','');
    }
  }
  Effect.Puff(id, {queue:'end'});
  if (i!=1) Effect.Appear(id, {queue:'end'});
}
function flmd()
{
  sels = getsels();
  if (moid == '')
  {
    deselall();
    document.getElementById('flist').onmousemove = flmm;
  }
}
function flmu()
{
  document.getElementById('flist').onmousemove = flmm;
}
function flmm()
{
  
}
function flm2c()
{
  if (moid == '') selall();
}

function mcm(id)
{
  var tmp = '';
  var ie5=document.all&&document.getElementById;
  if (!issel(id))
  {
    deselall();
    sel(id);
  }
  if (getsels().length == 1)
  {
    switch (parseInt(fli(id2i(id),'ft')))
    {
    case 1: // Folder
      tmp = '<div><a href="javascript:ajax_rq(\''+d+fli(i,'n')+'\');"><strong>Open</strong></a></div>';
      break;
    case 2: // HTML
      tmp = '<div><a href="javascript:file_view(\''+d+fli(id2i(id),'n')+'\');"><strong>View</strong></a></div><div><a href="javascript:file_open(\'preview\',\''+d+fli(id2i(id),'n')+'\');">Preview source</a></div><div><a href="javascript:file_open(\'textedit\',\''+d+fli(id2i(id),'n')+'\');">Edit</a></div><div><a>Edit with TinyMCE</a></div><div><a href="javascript:file_download(\''+d+fli(id2i(id),'n')+'\')">Download</a></div>';
      break;
    case 3: // PHP
      tmp = '<div><a href="javascript:file_view(\''+d+fli(id2i(id),'n')+'\');"><strong>View</strong></a></div><div><a href="javascript:file_open(\'preview\',\''+d+fli(id2i(id),'n')+'\');">Preview source</a></div><div><a href="javascript:file_open(\'textedit\',\''+d+fli(id2i(id),'n')+'\');">Edit</a></div><div><a href="javascript:file_download(\''+d+fli(id2i(id),'n')+'\')">Download</a></div>';
      break;
    case 4: // Text
      tmp = '<div><a href="javascript:file_open(\'preview\',\''+d+fli(id2i(id),'n')+'\');"><strong>Preview</strong></a></div><div><a href="javascript:file_view(\''+d+fli(id2i(id),'n')+'\');">View</a></div><div><a>Edit</a></div><div><a href="javascript:file_download(\''+d+fli(id2i(id),'n')+'\')">Download</a></div>';
      break;
    case 5: // Image
      tmp = '<div><a href="javascript:file_open(\'preview\',\''+d+fli(id2i(id),'n')+'\');"><strong>Preview</strong></a></div><div><a href="javascript:file_view(\''+d+fli(id2i(id),'n')+'\');">View</a></div><div><a href="javascript:file_download(\''+d+fli(id2i(id),'n')+'\')">Download</a></div>';
      break;
    case 6:
    default: // Unknown
      tmp = '<div><a><strong href="javascript:file_view(\''+d+fli(id2i(id),'n')+'\');">View?</strong></a></div><div><a href="javascript:file_download(\''+d+fli(id2i(id),'n')+'\')">Download</a></div>';
      break;
    }
    document.getElementById('cmenu').innerHTML = tmp+'<div class="hr"><hr /></div><div><a class="disabled">Cut</a></div><div><a class="disabled">Copy</a></div><div class="hr"><hr /></div><div><a href="javascript:file_rename(\''+id+'\');">Rename</a></div><div><a href="javascript:file_delete();">Delete</a></div>';
  }
  else
    document.getElementById('cmenu').innerHTML = '<div><a class="disabled">Cut</a></div><div><a class="disabled">Copy</a></div><div class="hr"><hr /></div><div><a href="javascript:file_delete();">Delete</a></div>';
}
function flmcm(e)
{
  var ie5=document.all&&document.getElementById;
  document.getElementById('cmenu').style.left = '' + (ie5?event.clientX:e.clientX) + 'px';
  document.getElementById('cmenu').style.top = '' + (ie5?event.clientY:e.clientY) + 'px';
  document.getElementById('cmenu').style.display = 'block';
  if (pw<(ie5?event.clientX:e.clientX)+document.getElementById('cmenu').offsetWidth)
    document.getElementById('cmenu').style.left = '' + (ie5?(event.clientX-document.getElementById('cmenu').offsetWidth):(e.clientX-document.getElementById('cmenu').offsetWidth)) + 'px';
  if (ph<(ie5?event.clientY:e.clientY)+document.getElementById('cmenu').offsetHeight)
    document.getElementById('cmenu').style.top = '' + (ie5?(event.clientY-document.getElementById('cmenu').offsetHeight):(e.clientY-document.getElementById('cmenu').offsetHeight)) + 'px';
  document.onclick = cmenuclose;
}

/*************************
 * File functions
 *************************/

function file_rename(id)
{
  var fname = fli(id2i(id),'n');
  if (fname.substring(fname.length-1,1)=='/') fname = fname.substring(0,fname.length-1);
  ext_confirm('Rename file','What would you like the new name of this file to be?</p><p>'+fl_melem(id2i(id))+'</p><p><input id="extv_f1" type="text" value="'+fname+'" />','ajax_act(\'ren&f1='+fli(id2i(id),'n')+'&f2=\'+document.getElementById(\'extv_f1\').value+\'/\')','');
}
function file_delete()
{
  var ids = '';
  var files = '';
  var sels = getsels();
  if (sels.length == 0) return;
  for (var i=0;i<sels.length;i++)
  {
    ids += '&f'+(i+1)+'='+fli(id2i(sels[i]),'n');
    files += fl_melem(id2i(sels[i]));
  }
  ext_confirm('Delete file'+(sels.length==1?'':'s'),'Are you sure you want to delete '+(sels.length==1?'this file':'these files')+'?</p><p>'+files,'ajax_act(\'del&f1='+fli(id2i(id),'n')+ids+')','');
}
function file_upload()
{
  if (state != 'f')
  {
    ext_alert('Error','You can only upload if you have a folder open.','');
    return;
  }
  ext_raw('Upload file','<iframe src="fileman_ext.php?upload&d='+d+s+'" frameborder="0" width="290" height="200" style="width:290px;height:200px;"></iframe>','');
}
function file_newdir()
{
  var fname = 'New Folder';
  var i = 1;
  while (name2id(fname)!='') fname = 'New Folder '+(++i);
  ext_confirm('New Folder','What would you like the name of the new folder to be?</p><p><input id="extv_f1" type="text" value="'+fname+'" />','ajax_act(\'ndir&f1\'+document.getElementById(\'extv_f1\').value)','');
}
function file_newfile()
{
  var fname = 'New Text Document.txt';
  var i = 1;
  while (name2id(fname)!='') fname = 'New Text Document '+(++i)+'.txt';
  ext_confirm('New File','What would you like the new name of this file to be?</p><p><input id="extv_f1" type="text" value="'+fname+'" />','ajax_act(\'nfile&f1=\'+document.getElementById(\'extv_f1\').value)','');
}
function file_open(app,df)
{
  window.open(app+'.php?d='+df+s,'_blank');
}
function file_view(df)
{
  window.open(p+df,'_blank');
}
function file_download(df)
{
  window.open('fileman_ext.php?down&d='+df+s,'_blank');
}


/*************************
 * Settings
 *************************/

var fl_type = 0;
// 0 - icons
// 1 - thumbnails
// 2 - tiles
// 3 - details
// 4 - list

function fm_about()
{
  ext_alert('About Filecharger','<strong><img src="images/logo.gif" alt="Filecharger" /></strong><br />Version '+fm_version+'<br /><br />Copyright &copy; 2006 Zarel','');
}


/*************************
 * Ajax
 *************************/

var filelist = false;
var fl = false;

var fl_c = 0;
var cl_c = 0;

function ajax_init(user,pass)
{
  if (user == '') user = 'guest';
  if (user == 'guest') pass = '';
  if (window.XMLHttpRequest)
  { // Mozilla, Safari,...
    filelist = new XMLHttpRequest();
  }
  else if (window.ActiveXObject)
  { // IE
    try
    {
      filelist = new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch (e)
    {
      try
      {
        filelist = new ActiveXObject("Microsoft.XMLHTTP");
      } catch (e) {}
    }
  }
  if (!filelist)
  {
    ext_alert('Error','Notice: Your browser does not support Ajax.</p><p>You will be automatically redirected to File Manager Basic.','location.replace(\'index.php\');');
    return false;
  }
  u = user;
  filelist.onreadystatechange = function() { ajax_chkstate(filelist); };
  filelist.open('POST', 'fileman_xml.php?init', true);
  filelist.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
  filelist.send('user='+user+'&pass='+pass);
}

function ajax_rq(data)
{
  filelist = false;

  if (window.XMLHttpRequest)
  { // Mozilla, Safari,...
    filelist = new XMLHttpRequest();
  }
  else if (window.ActiveXObject)
  { // IE
    try
    {
      filelist = new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch (e)
    {
      try
      {
        filelist = new ActiveXObject("Microsoft.XMLHTTP");
      } catch (e) {}
    }
  }

  filelist.onreadystatechange = function() { ajax_chkstate(filelist); };
  filelist.open('GET', 'fileman_xml.php?d='+data+s, true);
  filelist.send(null);
}
function ajax_act(action)
{
  if (window.XMLHttpRequest)
  { // Mozilla, Safari,...
    filelist = new XMLHttpRequest();
  }
  else if (window.ActiveXObject)
  { // IE
    try
    {
      filelist = new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch (e)
    {
      try
      {
        filelist = new ActiveXObject("Microsoft.XMLHTTP");
      } catch (e) {}
    }
  }
  filelist.onreadystatechange = function() { ajax_chkstate(filelist); };
  filelist.open('POST', 'fileman_xml.php?d='+data+s, true);
  filelist.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
  filelist.send('a='+action);
}

function ajax_chkstate(http_request)
{
  if (http_request.readyState == 4)
  {
    if (http_request.status == 200) {
      var xmldoc = http_request.responseXML;
      ajax_process(xmldoc);
    }
    else
    {
      fm_loaderr('The request did not go through.');
    }
  }
}

function ajax_process(xmldoc)
{
  fm_chgstate('f');
  var error = xmldoc.getElementsByTagName('err').item(0);
  if (error && error.firstChild.data=='to')
  {
    location.refresh();
    return;
  }
  else if (error && error.firstChild.data=='Access Denied.')
  {
    fm_loaderr('Error: Access Denied.');
    return;
  }
  else if (error)
  {
    fm_loaderr('There was a problem with the request.');
    return;
  }
  if (u != 'guest')
  {
    document.getElementById('fft_login').href='javascript:init();';
    document.getElementById('fft_login').innerHTML='Log out';
  }
  if (xmldoc.getElementsByTagName('s').item(0))
    s = '&s='+xmldoc.getElementsByTagName('s').item(0);
  else
    s = '';
  //rqt = xmldoc.getElementsByTagName('rqt').item(0).firstChild.data;
  fl = xmldoc.getElementsByTagName('fl').item(0);
  fl_c = fl.getElementsByTagName('fi').length;
  cl_c = fl.getElementsByTagName('ci').length;
  //xmldoc;
  
  p = xmldoc.getElementsByTagName('p').item(0).firstChild.data;
  d = '';
  if (xmldoc.getElementsByTagName('d').item(0).firstChild)
    d = xmldoc.getElementsByTagName('d').item(0).firstChild.data;
  var uls = '';
  var ula = p.split('/');
  var fs = '';
  for (var i=0; i<ula.length-1; i++)
  {
    if (i == ula.length-2)
      fs += '<a href="javascript:ajax_rq(\'\');void(0);">'+ula[i]+'</a>/';
    else
      fs += ula[i]+'/';
  }
  ula = d.split('/');
  for (var i=0; i<ula.length; i++)
  {
    if (ula[i] != '')
    {
      uls += ula[i]+'/';
      fs += '<a href="javascript:ajax_rq(\''+uls+'\');void(0);">'+ula[i]+'</a>/';
    }
    else
      fs += ula[i];
  }
  if (fs == '') fs = '&nbsp;';
  document.getElementById('dir').innerHTML = fs;
  
  if (d == '')
  {
    document.getElementById('su').className = 'd';
    document.getElementById('su_img').src = 'images/icons32/_udird.gif';
  }
  else
  {
    document.getElementById('su').className = 'n';
    document.getElementById('su_img').src = 'images/icons32/_udir.gif';
  }
  
  fm_refresh();
}

function fm_refresh()
{
  var tmp = '';
  for (var i=0; i<fl_c; i++)
  {
    tmp = tmp+'<li>'+fm_elem(i)+'</li>';
  }
  document.getElementById('flist').innerHTML = '<div id="fl" class="flw"><ul class="fl">'+tmp+'</ul></div>';
  if (fl_c==0)
    document.getElementById('flist').innerHTML = '<div id="fl" class="flw"><div class="fmsg">This folder is empty.</div></div>';
  tmp = '';
  for (var i=0; i<cl_c; i++)
  {
    tmp = tmp+'<li>'+fm_elem(-1-i)+'</li>';
  }
  document.getElementById('clist').innerHTML = '<div id="cl" class="flw"><ul class="fl">'+tmp+'</ul></div>';
  if (cl_c==0)
    document.getElementById('clist').innerHTML = '<div id="cl" class="flw"><div class="fmsg">The shelf is empty.</div></div>';
}

function fli(i,t)
{
  if (i>=0)
    return fl.getElementsByTagName('fi').item(i).getElementsByTagName(t).item(0).firstChild.data;
  return fl.getElementsByTagName('ci').item(-1-i).getElementsByTagName(t).item(0).firstChild.data;
}
function fliis(i,t)
{
  if (i>=0)
    return fl.getElementsByTagName('fi').item(i).getElementsByTagName('is').item(0).getElementsByTagName(t).item(0).firstChild.data;
  return fl.getElementsByTagName('ci').item(-1-i).getElementsByTagName('is').item(0).getElementsByTagName(t).item(0).firstChild.data;
}
function fci(t)
{
  return fl.getElementsByTagName('fc').item(0).getElementsByTagName(t).item(0).firstChild.data;
}

/*************************
 * Ext functions
 *************************/

function ext_alert(title,message,act)
{
  document.getElementById('ext_msg').innerHTML = '<p>'+message+'</p><p class="btns"><input type="button" value="OK" onclick="ext_close();'+act+'" /></p>';
  document.getElementById('ext_tbar').href = 'javascript:ext_close();'+act;
  document.getElementById('ext_title').innerHTML = title;
  document.getElementById('ext').style.display = 'block';
}
function ext_confirm(title,message,act,actc)
{
  document.getElementById('ext_msg').innerHTML = '<form action="fileman.html" onsubmit="ext_close();'+act+'"><p>'+message+'</p><p class="btns"><input type="submit" value="OK" onclick="ext_close();'+act+'" /> <input type="button" value="Cancel" onclick="ext_close();'+actc+'" /></p></form>';
  document.getElementById('ext_tbar').href = 'javascript:ext_close();'+actc;
  document.getElementById('ext_title').innerHTML = title;
  document.getElementById('ext').style.display = 'block';
}
function ext_ynconfirm(title,message,act,actc)
{
  document.getElementById('ext_msg').innerHTML = '<form action="fileman.html" onsubmit="ext_close();'+act+'"><p>'+message+'</p><p class="btns"><input type="submit" value="Yes" onclick="ext_close();'+act+'" /> <input type="button" value="No" onclick="ext_close()'+actc+';" /></p></form>';
  document.getElementById('ext_tbar').href = 'javascript:ext_close();'+actc;
  document.getElementById('ext_title').innerHTML = title;
  document.getElementById('ext').style.display = 'block';
}
function ext_yncconfirm(title,message,acty,actn,actc)
{
  document.getElementById('ext_msg').innerHTML = '<form action="fileman.html" onsubmit="ext_close();'+act+'"><p>'+message+'</p><p class="btns"><input type="submit" value="Yes" onclick="ext_close();'+acty+'" /> <input type="button" value="No" onclick="ext_close();'+actn+'" /> <input type="button" value="Cancel" onclick="ext_close();'+actc+'" /></p></form>';
  document.getElementById('ext_tbar').href = 'javascript:ext_close();'+actc;
  document.getElementById('ext_title').innerHTML = title;
  document.getElementById('ext').style.display = 'block';
}
function ext_raw(title,message,actc)
{
  document.getElementById('ext_msg').innerHTML = message;
  document.getElementById('ext_tbar').href = 'javascript:ext_close();'+actc;
  document.getElementById('ext_title').innerHTML = title;
  document.getElementById('ext').style.display = 'block';
}

function ext_close()
{
  document.getElementById('ext').style.display = 'none';
}
function ext_fclose()
{
  document.getElementById('ext_msg').innerHTML = '<p>Error displaying message.</p>';
  ext_close();
}

/*************************
 * Context Menu
 *************************/

function cmenuclose()
{
  document.getElementById('cmenu').innerHTML = '<div><a href="javascript:selall();">Select All</a></div><div><a href="javascript:selinv();">Invert Selection</a></div><div><a href="javascript:deselall();">Deselect All</a></div>';
  document.getElementById('cmenu').style.display = 'none';
  document.onclick = null;
}

/*************************
 * Sidebar
 *************************/

function toggle(elem)
{
  if (document.getElementById(elem).style.display == 'none')
  {
    document.getElementById(elem).style.display = 'block';
    document.getElementById(elem+'_exp').style.display = 'none';
    document.getElementById(elem+'_col').style.display = 'block';
  }
  else
  {
    document.getElementById(elem).style.display = 'none';
    document.getElementById(elem+'_exp').style.display = 'block';
    document.getElementById(elem+'_col').style.display = 'none';
  }
  document.getElementById(elem+'_a').blur();
}
