$.extend({
    fullScreen:function(id){
        var uresize=function(jo){
            var w=$(window).width();
            var h=$(window).height();
            jo.css({
                width:w,
                height:h
            });
        }
        var obj=$('#'+id);
        uresize(obj);
        $(window).bind('resize',function(){
            uresize(obj);
        });
    }
})


/*<?php apf_require_controller('Try_MergeMap')?>*/
var MId='map';
var bm=new BMap.Map(MId);
bm.disableDragging();
bm.disableScrollWheelZoom();
bm.disableDoubleClickZoom();
bm.disableKeyboard();
bm.disableInertialDragging();
bm.disableContinuousZoom();
bm.disablePinchToZoom();

bm.centerAndZoom($('#cityname').val(),11);

//自定义覆盖物
var point = new BMap.Point(121.402025,31.108651);
var canvas;
UOverlay=function(){}
UOverlay.prototype=new BMap.Overlay();
UOverlay.prototype.initialize=function(map){
    this._map=map;
    var canvas=this._canvas=document.createElement('canvas');
    var divMap=document.getElementById(MId);
    canvas.style.position='absolute';
    canvas.height=divMap.clientHeight;
    canvas.width=divMap.clientWidth;
    map.getPanes().labelPane.appendChild(canvas);
    return canvas;
}
var god=1;
UOverlay.prototype.draw=function(){
    if(god==1){
        area=bm.getBounds();
        //        var p=[],area;
        //        for(var i=0;i<5000;i++){
        //            p[i]={
        //                lat:(area._swLat+(area._neLat-area._swLat)*Math.random()).toFixed(6),
        //                lng:(area._swLng+(area._neLng-area._swLng)*Math.random()).toFixed(6)
        //            };
        //        }
        if(data){
            var canvas=this._canvas;
            var map=this._map;
            var cache={};
            var len=data.length;
            if(canvas){
                var con=canvas.getContext('2d');
                var cc={};
                cc.height=20;
                cc.width=100;
                for(var i=0;i<colorRange.length;i++){
                    con.fillStyle='rgba('+colorRange[i][0]+','+colorRange[i][1]+','+colorRange[i][2]+',0.7)';
                    con.fillRect(890,i*cc.height+10,cc.width,cc.height);
                    con.fillStyle='black';
                    con.font='18px Times New Roman';
                    con.fillText(colorRange[i]['t'],890,cc.height-3+i*cc.height+10);
                }
                for(var i=0;i<len;i++){
                    var pos=map.pointToOverlayPixel(data[i][0]);
                    var r = data[i][1][0];
                    var g = data[i][1][1];
                    var b = data[i][1][2];
                    var alp = 0.7;
                    var radgrad = con.createRadialGradient(pos.x, pos.y, 1, pos.x, pos.y, 8);
                    radgrad.addColorStop( 0, 'rgba(' + r + ',' + g + ','+ b + ',' + alp + ')');
                    radgrad.addColorStop( 1, 'rgba(' + r + ',' + g + ','+ b + ',0)');
                    con.fillStyle = radgrad;
                    con.fillRect(pos.x - 8, pos.y - 8, 16, 16);
                }
                var img=canvas.toDataURL("image/png");
//                bindSave(img);
            }
        }
    }
    god++;
}

bm.addEventListener('load',function(){
    var myOverlay = new UOverlay();
    bm.addOverlay(myOverlay);
})

bindSave=function(img){
    if(cityid){
        $('#save').click(function(){
            var d=new Date();
            var t=d.getTime();
            $('#map').hide(200);
            $('#save').hide(200);
            $('#tip').show(200);
            if(img){
                $.ajax({
                    url:"MergeMap.php",
                    data:img,
                    type:'POST',
                    contentType:'application/upload',
                    success:function(d){
                        if(d){
                            $('#tip').hide(200);
                            $('#merge_pic').attr('src', d);
                            $('#merge_pic').show(200);
                        }
                    }
                });
            }
        });
    }
}