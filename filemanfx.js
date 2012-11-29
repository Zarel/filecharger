var anim = null;

function fme_puff(eid)
{
  var elem = document.getElementById(eid);
  var elemx = 0, elemy = 0;
  if (elem.offsetParent) {
    elemx = elem.offsetLeft; elemy = elem.offsetTop;
    while (elem = elem.offsetParent) {
      elemx += elem.offsetLeft; elemy += elem.offsetTop;
    }
  }
  document.getElementById('afla').style.position = 'absolute';
  document.getElementById('afla').style.left = ''+elemx+'px';
  document.getElementById('afla').style.top = ''+elemy+'px';
  document.getElementById('afla').innerHTML = document.getElementById(eid).innerHTML;
  document.getElementById('afla').style.display = 'block';
  document.getElementById(eid).style.display = 'none';
  anim = setTimeout("fme_puff_ani('"+eid+"',0)",50);
}

function fme_puff_ani(eid,amt)
{
  document.getElementById('afla').style.opacity = (1000-amt)/1000;
  document.getElementById('afla').style.filter = 'alpha(opacity=' + (1000-amt)/10 + ')';
  var mult = 1.0 + 0.001*amt;
  document.getElementById('afla').style.fontSize = ''+round(11*mult)+'px';
  document.getElementById('afla').style.borderWidth = ''+round(1*mult)+'px';
  document.getElementById('afla').style.width = ''+round(66*mult)+'px';
  document.getElementById('afla').style.height = ''+round(48*mult)+'px';
  if (amt >= 1000)
  {
    document.getElementById('afla').innerHTML = '';
    if (document.getElementById(id))
      document.getElementById(id).style.display = 'block';
    document.getElementById('afla').style.display = 'none';
  }
  else
    anim = setTimeout("fme_puff_ani('"+eid+"',"+(amt+50)+");",50);
}