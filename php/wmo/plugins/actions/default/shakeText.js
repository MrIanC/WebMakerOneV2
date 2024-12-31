{
    var $this = $(clickedAction);
    $this.css({ position: 'relative' });

    // Chain the animation for shake effect
    $this.animate({ left: '-10px' }, 100)
        .animate({ left: '10px' }, 100)
        .animate({ left: '-10px' }, 100)
        .animate({ left: '10px' }, 100)
        .animate({ left: '0px' }, 100); // Reset position
}