/*=========================================================================================
    File Name: pickers.js
    Description: Pick a date/time Picker, Date Range Picker JS
    ----------------------------------------------------------------------------------------
    Item Name: Vuexy  - Vuejs, HTML & Laravel Admin Dashboard Template
    Author: Pixinvent
    Author URL: http://www.themeforest.net/user/pixinvent
==========================================================================================*/
(function (window, document, $) {
  'use strict';

  /*******  Flatpickr  *****/
  var basicPickr = $('.flatpickr-basic'),
    timePickr = $('.flatpickr-time'),
    dateTimePickr = $('.flatpickr-date-time'),
    multiPickr = $('.flatpickr-multiple'),
    rangePickr = $('.flatpickr-range'),
    humanFriendlyPickr = $('.flatpickr-human-friendly'),
    disabledRangePickr = $('.flatpickr-disabled-range'),
    inlineRangePickr = $('.flatpickr-inline');

  // Default
  basicPickr.each(function () {
    $(this).flatpickr();
  });

  // Time
  timePickr.each(function () {
    $(this).flatpickr({
      enableTime: true,
      noCalendar: true
    });
  });

  // Date & Time
  dateTimePickr.each(function () {
    $(this).flatpickr({
      enableTime: true
    });
  });

  // Multiple Dates
  multiPickr.each(function () {
    $(this).flatpickr({
      weekNumbers: true,
      mode: 'multiple',
      minDate: 'today'
    });
  });

  // Range
  rangePickr.each(function () {
    $(this)
      .attr('placeholder', 'DD-MM-YYYY to DD-MM-YYYY') // Set placeholder
      .flatpickr({
        mode: 'range',
        dateFormat: 'd-m-Y',
        onClose: function(selectedDates, dateStr, instance) {
          // If only a single date is picked (user didn't pick an end date)
          if (selectedDates.length === 1) {
            // Set both start and end to the same selected date
            instance.setDate([selectedDates[0], selectedDates[0]], true);
          }
        }
      });
  });


  // Human Friendly
  humanFriendlyPickr.each(function () {
    $(this).flatpickr({
      altInput: true,
      altFormat: 'F j, Y',
      dateFormat: 'Y-m-d'
    });
  });

  // Disabled Range
  disabledRangePickr.each(function () {
    $(this).flatpickr({
      dateFormat: 'Y-m-d',
      disable: [
        {
          from: new Date().fp_incr(2),
          to: new Date().fp_incr(7)
        }
      ]
    });
  });

  // Inline
  inlineRangePickr.each(function () {
    $(this).flatpickr({
      inline: true
    });
  });

  /*******  Pick-a-date Picker  *****/
  // Basic date
  if($('.pickadate').length) {
    $('.pickadate').pickadate();
  }

  // Format Date Picker
  $('.format-picker').each(function () {
    $(this).pickadate({
      format: 'mmmm, d, yyyy'
    });
  });

  // Date limits
  $('.pickadate-limits').each(function () {
    $(this).pickadate({
      min: [2019, 3, 20],
      max: [2019, 5, 28]
    });
  });

  // Disabled Dates & Weeks
  $('.pickadate-disable').each(function () {
    $(this).pickadate({
      disable: [1, [2019, 3, 6], [2019, 3, 20]]
    });
  });

  // Picker Translations
  $('.pickadate-translations').each(function () {
    $(this).pickadate({
      formatSubmit: 'dd/mm/yyyy',
      monthsFull: [
        'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
      ],
      monthsShort: ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec'],
      weekdaysShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
      today: "aujourd'hui",
      clear: 'clair',
      close: 'Fermer'
    });
  });

  // Month Select Picker
  $('.pickadate-months').each(function () {
    $(this).pickadate({
      selectYears: false,
      selectMonths: true
    });
  });

  // Month and Year Select Picker
  $('.pickadate-months-year').each(function () {
    $(this).pickadate({
      selectYears: true,
      selectMonths: true
    });
  });

  // Short String Date Picker
  $('.pickadate-short-string').each(function () {
    $(this).pickadate({
      weekdaysShort: ['S', 'M', 'Tu', 'W', 'Th', 'F', 'S'],
      showMonthsShort: true
    });
  });

  // Change first weekday
  $('.pickadate-firstday').each(function () {
    $(this).pickadate({
      firstDay: 1
    });
  });

  /*******    Pick-a-time Picker  *****/
  // Basic time
  $('.pickatime').each(function () {
    $(this).pickatime();
  });

  // Format options
  $('.pickatime-format').each(function () {
    $(this).pickatime({
      // Escape any “rule” characters with an exclamation mark (!).
      format: 'T!ime selected: h:i a',
      formatLabel: 'HH:i a',
      formatSubmit: 'HH:i',
      hiddenPrefix: 'prefix__',
      hiddenSuffix: '__suffix'
    });
  });

  // Format options with dynamic labels
  $('.pickatime-formatlabel').each(function () {
    $(this).pickatime({
      formatLabel: function (time) {
        var hours = (time.pick - this.get('now').pick) / 60,
          label = hours < 0 ? ' !hours to now' : hours > 0 ? ' !hours from now' : 'now';
        return 'h:i a <sm!all>' + (hours ? Math.abs(hours) : '') + label + '</sm!all>';
      }
    });
  });

  // Min - Max Time to select
  $('.pickatime-min-max').each(function () {
    $(this).pickatime({
      // Using JavaScript Date objects
      min: new Date(2015, 3, 20, 7),
      max: new Date(2015, 7, 14, 18, 30)

      // Using Array
      // min: [7,30],
      // max: [14,0]
    });
  });

  // Intervals
  $('.pickatime-intervals').each(function () {
    $(this).pickatime({
      interval: 150
    });
  });

  // Disable Time
  $('.pickatime-disable').each(function () {
    $(this).pickatime({
      disable: [
        // Disable Using Integers
        3, 5, 7, 13, 17, 21

        /* Using Array */
        // [0,30],
        // [2,0],
        // [8,30],
        // [9,0]
      ]
    });
  });

  // Close on a user action
  $('.pickatime-close-action').each(function () {
    $(this).pickatime({
      closeOnSelect: false,
      closeOnClear: false
    });
  });

})(window, document, jQuery);