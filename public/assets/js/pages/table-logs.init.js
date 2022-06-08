/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!***********************************************!*\
  !*** ./resources/js/pages/table-logs.init.js ***!
  \***********************************************/
/******/
(function () {
  // webpackBootstrap

  /******/
  "use strict";

  var __webpack_exports__ = {};
  /*!***********************************************!*\
    !*** ./resources/js/pages/table-logs.init.js ***!
    \***********************************************/

  (function () {
    $('#datatable').DataTable({
      order: [[0, "asc"]],
      processing: true,
      serverSide: false,
      paging: true,
      ajax: {
        url: "/loglist/",
        type: "GET"
      },
      columns: [{
        data: 'id',
        className: "text-center"
      }, {
        data: 'code',
        className: "text-center"
      }, {
        data: 'created_at',
        className: "text-center"
      }, {
        data: 'error',
        className: "text-center"
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
      var action = $(this).attr('name');
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
  /******/

})();
/******/ })()
;