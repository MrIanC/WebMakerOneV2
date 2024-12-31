{
    var $this = $(clickedAction);
    $this.css({ position: 'relative' });

    $this.css({ position: 'relative' });

    $this.animate({ deg: -5 }, {
        duration: 100,
        step: function (now) {
            $this.css({
                transform: 'rotate(' + now + 'deg)'
            });
        }
    }).animate({ deg: 5 }, {
        duration: 100,
        step: function (now) {
            $this.css({
                transform: 'rotate(' + now + 'deg)'
            });
        }
    }).animate({ deg: -5 }, {
        duration: 100,
        step: function (now) {
            $this.css({
                transform: 'rotate(' + now + 'deg)'
            });
        }
    }).animate({ deg: 5 }, {
        duration: 100,
        step: function (now) {
            $this.css({
                transform: 'rotate(' + now + 'deg)'
            });
        }
    }).animate({ deg: 0 }, {
        duration: 100,
        step: function (now) {
            $this.css({
                transform: 'rotate(' + now + 'deg)'
            });
        }
    });
}