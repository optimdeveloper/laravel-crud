/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
var __webpack_exports__ = {};
/*!*************************************************!*\
  !*** ./resources/js/pages/table-events.init.js ***!
  \*************************************************/


(function () {
  $('#datatable').DataTable({
    order: [[0, "asc"]],
    processing: true,
    serverSide: false,
    paging: true,
    ajax: {
      url: "/eventlist/",
      type: "GET"
    },
    columns: [{
      data: 'id',
      className: "text-center"
    }, {
      data: 'name',
      className: "text-center"
    }, {
      data: 'date_time',
      className: "text-center"
    }, {
      data: 'location',
      className: "text-center"
    }, {
      data: 'privacy',
      className: "text-center"
    }, {
      data: 'published',
      className: "text-center"
    }, {
      data: 'focused_on_gender',
      className: "text-center"
    }, {
      data: 'focused_on_age_range',
      className: "text-center"
    }, {
      data: null,
      className: "text-center",
      render: function render(data) {
        return '<a href="/event/cancel/' + data['id'] + '" class="text-danger2 sweet-warning delete"><i class="mdi mdi-sync-alert font-size-18" id="sa-warning"></i></a>';
      }
    }, {
      data: null,
      className: "text-center",
      render: function render(data) {
        return '<a href="/event/' + data['id'] + '" class="text-success"><i class="mdi mdi-account-details-outline font-size-18"></i></a>';
      }
    }],
    drawCallback: function drawCallback() {
      initLoad();
      sweetWarningToggle();
    }
  });
})();

function initLoad() {
  $('.load').click(function (e) {
    showLoad();
  });
}

function sweetWarningToggle() {
  $('.sweet-warning').click(function (e) {
    e.preventDefault();
    var action = $(this).attr('href');
    Swal.fire({
      title: "Are you sure?",
      icon: "warning",
      iconColor: "#2c666e",
      showCancelButton: true,
      confirmButtonColor: "#2c666e",
      cancelButtonColor: "#90ddf0",
      confirmButtonText: "Yes, toggle it!"
    }).then(function (result) {
      if (result.value) {
        showLoad();
        window.location.href = action; // Swal.fire("Deleted!", "Your file has been deleted.", "success");
      }
    });
    return false;
  });
}

function showLoad() {
  $('#status').show();
  $('#preloader').fadeIn('slow');
}
/******/ })()
;