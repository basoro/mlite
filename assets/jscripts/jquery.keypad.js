;(function($) {
  (function(pluginName) {
    var defaults = {
      inputField: 'input.keypad',
      buttonTemplate: '<button></button>',
      submitButtonText: 'ok',
      deleteButtonText: 'del',
      submitButtonClass: 'submit',
      deleteButtonClass: 'delete'
    };
    $.fn[pluginName] = function(options) {
      options = $.extend(true, {}, defaults, options);
            
      return this.each(function() {
        var elem = this,
          $elem = $(elem),
          $input = jQuery.type(options.inputField) == 'string' ? $(options.inputField) : options.inputField,
          $form = $input.parents('form').length ? $($input.parents('form')[0]) : $elem;

        var numbers = Array.apply(null, Array(9)).map(function (_, i) {
          return $(options.buttonTemplate).html(i+1).addClass('number');
        });
        numbers.push($(options.buttonTemplate).html(options.deleteButtonText).addClass(options.deleteButtonClass));
        numbers.push($(options.buttonTemplate).html("0").addClass('number'));
        numbers.push($(options.buttonTemplate).html(options.submitButtonText).addClass(options.submitButtonClass));
        $elem.html(numbers).addClass('keypad');

        $elem.find('.number').click(function(e) {
          $input.val($input.val() + $(e.target).text());
          $input.trigger('change');
        });
        $elem.find('.' + options.deleteButtonClass).click(function(e) {
          $input.val($input.val().slice(0, -1));
          $input.trigger('change');
        });
        $elem.find('.' + options.submitButtonClass).click(function(e) {
          $form.submit();
        });
      });
    };
    $.fn[pluginName].defaults = defaults;
  })('keypad');
})(jQuery);