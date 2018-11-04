
$( document ).ready(function() {
    $('#admin-table').DataTable({
        'paging'      : true,
        'lengthChange': true,
        'searching'   : true,
        'ordering'    : true,
        'info'        : true,
        'autoWidth'   : true,
        "columnDefs": [
            { "orderable": false, "targets": 5 },
            { "orderable": false, "targets": 8 }
        ]
    });

    $('.expatriate-toggle').change(function() {
        var expatriate = ($(this).prop('checked') ? '1' : '0');
        var id= $(this).data("id");

        $.post( Routing.generate('leaderChangeExpatriate', {id: id, expatriate:expatriate}), function( data ) {
            if(data.code === 403) {
                $('#expatriate-toggle-'+id).bootstrapToggle('toggle');
                alert('Warning: ' + data.error);
            }
        });
    });

    $("#assign-country").val(initialCountryUserAdministrators).trigger('change');
    $("#edit-admin-assign-country").val(initialCountryUserAdministrators).trigger('change');

    if($("#ROLE_ADMIN_COUNTRY").prop('checked')){
        $("#country-selector").show();
    } else{
        $("#country-selector").hide();
    }
});

$(".picture-modal").on("click", function() {
    var id= $(this).data("id");
    var text= $(this).data("text");

    $('#modal-picture-src').attr('src', $('#image-src-'+id).attr('src'));
    $('#modal-picture-text').html(text);
    $('#modal-picture').modal('show');
});

$('.edit-admin-role-toggle').change(function() {
    var status = ($(this).prop('checked') ? '1' : '0');
    var leaderId= $(this).data("leader-id");
    var roleId= $(this).data("role-id");
    var roleAdminStatus = ($("#ROLE_ADMIN").prop('checked') ? '1' : '0');
    var roleAdminCountryStatus = ($("#ROLE_ADMIN_COUNTRY").prop('checked') ? '1' : '0');

    if(roleAdminStatus == 1 && roleAdminCountryStatus  == 1) {
        if($(this).attr('id') == 'ROLE_ADMIN') {
            $("#ROLE_ADMIN_COUNTRY").bootstrapToggle('toggle');
        }
        if($(this).attr('id') == 'ROLE_ADMIN_COUNTRY') {
            $("#ROLE_ADMIN").bootstrapToggle('toggle');
        }
    }

    if($("#ROLE_ADMIN_COUNTRY").prop('checked')){
        $("#country-selector").show('fast');
    } else{
        $("#country-selector").hide('fast');
    }

    $.post( Routing.generate('leaderChangeRole', {leaderId: leaderId, roleId: roleId, status:status}), function( data ) {
        if(data.code === 403) {
            $(this).bootstrapToggle('toggle');
            alert('Warning: ' + data.error);
        }
    });
});

$("#assign-country").select2({
    multiple:true,
    allowClear:true,
    placeholder:"Select one or more countries"
});

$("#edit-admin-assign-country").select2({
    multiple:true,
    allowClear:true,
    placeholder:"Select one or more countries"
}).on('change', function (e) {
    var countries = $(this).val();
    var leaderId= $(this).data("leader-id");
    $.post( Routing.generate('adminChangeCountries', {id: leaderId, countries:countries}), function( data ) {

    });
});

$(".invite-admin-role-toggle").change(function() {
    var roleAdminStatus = ($("#ROLE_ADMIN").prop('checked') ? '1' : '0');
    var roleAdminCountryStatus = ($("#ROLE_ADMIN_COUNTRY").prop('checked') ? '1' : '0');

   if((roleAdminStatus == 0 && roleAdminCountryStatus  == 0) || (roleAdminStatus == 1 && roleAdminCountryStatus  == 1)) {
       if($(this).attr('id') == 'ROLE_ADMIN') {
           $("#ROLE_ADMIN_COUNTRY").bootstrapToggle('toggle');
       }
       if($(this).attr('id') == 'ROLE_ADMIN_COUNTRY') {
           $("#ROLE_ADMIN").bootstrapToggle('toggle');
       }
   }

   if($("#ROLE_ADMIN_COUNTRY").prop('checked')){
       $("#country-selector").show('fast');
   } else{
       $("#country-selector").hide('fast');
   }
});

$("#invite-admin-form").on("submit", function(e) {
    e.preventDefault();

    var email = $("#admin-email").val();
    var roleAdmin = ($("#ROLE_ADMIN").prop('checked') ? '1' : '0');
    var roleAdminCountry = ($("#ROLE_ADMIN_COUNTRY").prop('checked') ? '1' : '0');
    var countries = $("#assign-country").val();

    $.post( Routing.generate('adminInviteSave', {email: email, roleAdmin:roleAdmin, roleAdminCountry:roleAdminCountry, countries:countries}), function( data ) {
        if(data!=='ko') {
            document.location.href = Routing.generate('adminList');
        }
    });
});