$(document).ready(function() {
    //alert('sss');
    $("#dropdown-toggle").click(function(){
       // console.log('ssss');
        $('li.dropdown').toggleClass('open');
    })

    $('#change-password').validate({
        rules:{
            current_password:{
                required:true
            },
            new_password:{
                required:true,
                minlength: 6
            },
            re_new_password:{
                required:true,
                equalTo:'#new_password'
            }
        }
    });
    /*
     * does not close the waiting ... please remake
    $(document).ajaxStart($.blockUI({ css: {
        border: 'none',
        padding: '15px',
        backgroundColor: '#000',
        '-webkit-border-radius': '10px',
        '-moz-border-radius': '10px',
        opacity: .5,
        color: '#fff'
    } })).ajaxStop($.unblockUI);
    */



    addCssClassesForSomeElements();

});


function addCssClassesForSomeElements(){
    $(document).ajaxStop(function () {
        var shedulerGrid = $("div[data-role='scheduler']");
        if(shedulerGrid.length!=0){
            shedulerGrid.addClass('scheduler-buses-plan');
            var parent = shedulerGrid.closest('.w2ui-panel');
            parent.addClass('scheduler-buses-plan-parent');
            parent.next('.w2ui-resizer').on('mouseleave', function(){
                var poz = $(this).position();
                var planToResizeHeight = shedulerGrid.find('.k-scheduler-content');
                planToResizeHeight.height(poz.top-180);
            });
        }
    });

}