<script type="text/javascript">
    
    var eForReset = <?php echo $eeprom['servo_endstop']['e']?>;
    var rForReset = <?php echo $eeprom['servo_endstop']['r']?>;
    
    $(function () {

        $(".probe-action").on('click', handleProbe);
        $(".reset").on('click', handleReset);
        
    });
    
    /* */
    function handleProbe()
    {
        var button = $(this);
        var action = button.attr('data-action');
        
        if(action == 'open')  gCodeValue = $("#extend_value").val();
        if(action == 'close') gCodeValue = $("#retract_value").val();
        
        doCommand(action, gCodeValue);

    }
    
    /* */
    function handleReset()
    {
        var button = $(this);
        var action = button.attr('data-action');

        if(action == 'open') {
             gCodeValue = eForReset;
             $("#extend_value").val(eForReset);
        }
        if(action == 'close'){
             gCodeValue = rForReset;
             $("#retract_value").val(rForReset);
        }

        doCommand(action, gCodeValue);
    }
    
    /* */
    function doCommand(action, value)
    {
        $.ajax({
            type: "POST",
            url: "<?php echo site_url("probe") ?>/"
                + action + "/"
                + value,
            dataType: "json"
        }).done(function( data ) {
            
        });
        //~ var gCodeCommand = 'M711';
        //~ var probeCommand = 'M401';
        
        //~ if(action == 'close'){
            //~ gCodeCommand = 'M712';
            //~ probeCommand = 'M402';
        //~ }
        //var gcode = gCodeCommand+' S'+value+'\n'+probeCommand;
        //~ jog_make_call_ajax('mdi', gcode, actionCallBack);
    }
    
</script>
