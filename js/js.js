// JavaScript Document

function MM_jumpMenu(targ, selObj, restore) { //v3.0
    eval(targ + ".location='" + selObj.options[selObj.selectedIndex].value + "'");
    if (restore) selObj.selectedIndex = 0;
}

function mascara(w,e,m,r,a){
    if (!e) var e = window.event
    if (e.keyCode) code = e.keyCode;
    else if (e.which) code = e.which;

    var txt  = (!r) ? w.value.replace(/[^\d]+/gi,'') : w.value.replace(/[^\d]+/gi,'').reverse();
    var mask = (!r) ? m : m.reverse();
    var pre  = (a ) ? a.pre : "";
    var pos  = (a ) ? a.pos : "";
    var ret  = "";
    if(code == 9 || code == 8 || txt.length == mask.replace(/[^#]+/g,'').length) return false;

    for(var x=0,y=0, z=mask.length;x<z && y<txt.length;){
        if(mask.charAt(x)!='#'){
            ret += mask.charAt(x); x++; }
        else {
            ret += txt.charAt(y); y++; x++; } }

    ret = (!r) ? ret : ret.reverse()
    w.value = pre+ret+pos; }

String.prototype.reverse = function(){
    return this.split('').reverse().join('');
};