$(document).ready(function() {
   // prefill date dropdown with current date
   if ($('#Form_EventCalendarDate_Day').val() == 0) {
      var now = new Date();
      $('#Form_EventCalendarDate_Day').val(now.getDate());
      $('#Form_EventCalendarDate_Month').val(now.getMonth() + 1);
      $('#Form_EventCalendarDate_Year').val(now.getFullYear());
   }
   // toggle visibility of Calendar date depending on #Form_CategoryID
   var EventCalendarCategoryIDs = jQuery.parseJSON(gdn.definition('EventCalendarCategoryIDs'));
   $('#Form_CategoryID').change(function() {
      if (EventCalendarCategoryIDs.indexOf($('#Form_CategoryID').val()) > -1) {
         $('.EventCalendarInput').removeClass('Hidden');
      } else {
         $('.EventCalendarInput').addClass('Hidden');
      }
   });
});