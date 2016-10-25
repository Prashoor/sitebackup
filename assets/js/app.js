var table;
function confirmShow(message,complete) {
    var template = '<div id="confirm" class="alertbackdrop modalback">'+
        '<div class="matter"><span class="title">Please Confirm</span>' +
        '<div class="alertbody">' + message + '</div>' +
        '<div class="option-cont"><button id="confirmbtn" class="hidebtn">Confirm</button><button id="cancelbtn" class="hidebtn">Cancel</button></div>' +
    '</div>' +
    '</div>';
    
    $('#confirm').remove();
    $('body').append(template);
    
    $('#confirmbtn').click(function(){
        $('#confirm').remove();
        complete(true);
    });
    
    $('#cancelbtn').click(function(){
        $('#confirm').remove();
        complete(false);
    });
    
}

$(document).ready(function() {
 
    
    //datatables
    table = $('#videotable').DataTable({ 
 
        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        "order": [], //Initial no order.
 
        // Load data for the table's content from an Ajax source
        "ajax": {
            "url": "http://filmibaksa.com/api/all_videos",
            "type": "POST"
        },
 
        //Set column definition initialisation properties.
        "columnDefs": [ {
            "targets": -1,
            "data": null,
            "defaultContent": '<button class="edit">Edit</button><button class="delete">Delete</button>'
        } ],        
        "columns": [
            { data: 'id', searchable: false, sortable: false},
            { data: 'video_name', sortable: false},
            { data: 'description', sortable: false},
            { data: 'cost', searchable: false, sortable: false},
            { data: 'category', sortable: false},
            { data: 'created', searchable: false, sortable: false},
            { data: null, searchable: false, sortable: false}
        ]
 
    });
    
    $('#videotable tbody').on( 'click', 'button.delete', function () {
        var data = table.row( $(this).parents('tr') ).data();
        console.log('Data:' + JSON.stringify(data));
        confirmShow("Do you want to delete '" + data.video_name + "' ?",function(shoulddelete){
            if(shoulddelete) {
                $.get('http://filmibaksa.com/videoupload/delete_video/' + data.id, function( data ) {
                    data = JSON.parse(data);
                  if (data.success) {
                      table.ajax.reload();
                  }
                });
            }
        });
    } );
    
    $('#videotable tbody').on( 'click', 'button.edit', function () {
        var data = table.row( $(this).parents('tr') ).data();
        console.log('Data:' + JSON.stringify(data));
        $('div.editform').removeAttr('style');
        $('form#video_edit_form input[name="videoid"]').val(data.id);
        $('form#video_edit_form input[name="videoname"]').val(data.video_name);
        $('form#video_edit_form textarea[name="videodesc"]').val(data.description);
        
        var cst = parseInt(data.cost);
        var cost = ((cst === 0)?"free":"paid");
        $('form#video_edit_form select[name="cost"]').val(cost);
        $('form#video_edit_form select[name="category"]').val(data.category_id);
    });
    
    $('form#video_edit_form').submit(function(event) {
        event.preventDefault();
        var videoname = $('form#video_edit_form input[name="videoname"]').val();
        var description = $('form#video_edit_form textarea[name="videodesc"]').val();
        if (videoname.length === 0 || description.length === 0) {
            alertShow('Video Name and Description cannot be empty');
            return;
        }
        $(this).ajaxSubmit({ 
            success:function (response){
                response = JSON.parse(response);
                $('.editform').css('display', 'none');
                if(response.success) {
                    window.location.reload();
                    alertShow(message);
                }
                else if (response.error != null){
                    alertShow(error);
                }
            },
            resetForm: true 
        });
    });
 
});

// angular.module('datatable', ['datatables', 'ngRoute'])
// .controller('DataReloadWithAjaxCtrl', DataReloadWithAjaxCtrl);

// function DataReloadWithAjaxCtrl(DTOptionsBuilder, DTColumnBuilder) {
//     var vm = this;
//     vm.dtOptions = DTOptionsBuilder.fromSource('http://filmibaksa.com/api/all_videos')
//         .withOption('stateSave', true)
//         .withPaginationType('full_numbers');
//     vm.dtColumns = [
//         DTColumnBuilder.newColumn('id').withTitle('ID'),
//         DTColumnBuilder.newColumn('firstName').withTitle('First name'),
//         DTColumnBuilder.newColumn('lastName').withTitle('Last name').notVisible()
//     ];
//     vm.reloadData = reloadData;
//     vm.dtInstance = {};

//     function reloadData() {
//         var resetPaging = false;
//         vm.dtInstance.reloadData(callback, resetPaging);
//     }

//     function callback(json) {
//         console.log(json);
//     }
// }