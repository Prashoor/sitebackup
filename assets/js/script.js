function alertShow(message) {
    var template = '<div id="alert" class="alertbackdrop modalback">'+
        '<div class="matter"><span class="title">Alert</span>' +
        '<div class="alertbody">' + message + '</div>' +
        '<button id="alertbtn">Ok</button>' +
    '</div>' +
    '</div>';
    
    $('#alert').remove();
    $('body').append(template);
    scrollTop();
    $('#alertbtn').click(function(){
        $('#alert').remove();
        openScroll();
    });
}

function scrollTop(){
    document.body.scrollTop = document.documentElement.scrollTop = 0;
    $('body').addClass('no-scroll');
}

function openScroll(){
    $('body').removeClass('no-scroll');
}

var cnt = 0;

function videoShow(data) {
    var videotemp = '<div id="videoplayer" class="modalback"><div><button class="cross">X</button>' +
        '<video id="vjs-player' + cnt + '" class="video-js vjs-default-skin" controls preload="auto" width="640" height="264" poster="' + data.image + '" data-setup=\'{"example_option":true}\'>' +
                     '<source src="' + data.video + '" type="video/mp4" />' +
                     '<p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>' + 
                '</video></div>' +
    '</div>';
    scrollTop();
    $('#videoplayer').remove();
    $('body').append(videotemp);
    videojs('vjs-player'+ cnt);
    $('#videoplayer button.cross').click(function(){
        openScroll();
        $('#videoplayer').remove();
    });
    cnt++;
}

$(document).ready(function(){
    $('#refreshPage').click(function(){
        window.location.reload();    
    });
    
    $("input:file").change(function (){
        var fileName = $(this).val();
        $("label.fileupload").html(fileName);
    });
    
    $(document).on('click','.videocontainer', function(){
        videoShow($(this).data());
    });
    
    $('.popup.popup-loader').hide();
    
    $('form#video_upload_form').submit(function(event) {
        event.preventDefault();
        var videoname = $('form#video_upload_form input[name="videoname"]').val();
        var description = $('form#video_upload_form textarea[name="videodesc"]').val();
        if (videoname.length === 0 || description.length === 0) {
            alertShow('Video Name and Description cannot be empty');
            return;
        }
        $(this).ajaxSubmit({ 
            beforeSubmit: function() {
                $('.popup.popup-loader').show();
                $(".popup.popup-loader #video-uploader > span").width('0%');
                $('.popup.popup-loader .popup-content h2').html("Preparing your video..<br/>Don't refresh the page");
            },
            uploadProgress: function (event, position, total, percentComplete){	
                $(".popup.popup-loader #video-uploader > span").width(parseFloat(percentComplete) + '%');
                $('.popup.popup-loader .popup-content h2').text("Uploaded " + percentComplete + "%");
            },
            success:function (response){
                $('.uploadform').css('display', 'none');
                $('.popup.popup-loader').hide();
                openScroll();
                if(response.success) {
                    alertShow(message);
                }
                else if (response.error != null){
                    alertShow(error);
                }
                setTimeout(function(){
                    window.location.reload();
                }, 1000)
            },
            resetForm: true 
        });
    });
    
    $('.closeuploader').click(function(ev){
        ev.preventDefault;
        openScroll();
        $('.uploadform').css('display', 'none');
        $('.editform').css('display', 'none');
    });
    
    $('.upload-video').on('click', function(){
        scrollTop();
        $('.uploadform').removeAttr('style');
    })
    
    $('div.header ul.nav li a').on('click', function() {
        var tab = $(this).attr('href');
        
        $('div.container').removeClass('show');
        $('div.header ul.nav li a').removeClass('selected');
        $(this).addClass('selected');
        $('div' + tab + '.container').addClass('show');
    })
    
   
})