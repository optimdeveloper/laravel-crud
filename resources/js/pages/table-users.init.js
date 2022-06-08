'use strict';

(function () {
    $('#datatable').DataTable({
        order: [[0, "asc"]],
        processing: true,
        serverSide: false,
        paging: true,
        ajax: {
            url: "/userlist/",
            type: "GET"
        },
        columns: [{
            data: 'id',
            className: "text-center"
        }, {
            data: 'name',
            className: "text-center"
        }, {
            data: 'email',
            className: "text-center"
        }, {
            data: 'phone_number',
            className: "text-center"
        }, {
            data: 'birthday_at',
            className: "text-center"
        }, {
            data: 'gender',
            className: "text-center"
        }, {
            data: null,
            className: "text-center",
            render: function render(data) {
                return '<a href="/user/destroyCascade/' + data['id'] + '" class="text-danger sweet-warning delete"><i class="mdi mdi-trash-can-outline font-size-18" id="sa-warning"></i></a>';
            }
        }, {
            data: null,
            className: "text-center",
            render: function render(data) {
                return '<a href="/user/' + data['id'] + '" class="text-success"><i class="mdi mdi-account-details-outline font-size-18"></i></a>';
            }
        }],
        drawCallback: function drawCallback() {
            initLoad();
            sweetWarning();
        }
    });
})();

function initLoad() {
    $('.load').click(function (e) {
        showLoad();
    });
}

function sweetWarning() {
    $('.sweet-warning').click(function (e) {
        e.preventDefault();
        let action = $(this).attr('href');
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            iconColor: "#2c666e",
            showCancelButton: true,
            confirmButtonColor: "#2c666e",
            cancelButtonColor: "#90ddf0",
            confirmButtonText: "Yes, delete it!"
        }).then(function (result) {
            if (result.value) {
                showLoad();
                window.location.href = action;

                // Swal.fire("Deleted!", "Your file has been deleted.", "success");
            }
        });

        return false;
    });
}

function showLoad() {
    $('#status').show();
    $('#preloader').fadeIn('slow');
}
