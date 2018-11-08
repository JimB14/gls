
// two pages each use 2 instances of datepicker (Admin/Armalaser/Update/coupon.html and Admin/Armalaser/Add/coupon.html)
$(function() {
    $( "#datepicker1" ).datepicker({
      dateFormat: 'yy-mm-dd'
    });
    $( "#anim" ).change(function() {
      $( "#datepicker1" ).datepicker( "option", "showAnim", $( this ).val() );
    });

    $( "#datepicker2" ).datepicker({
      dateFormat: 'yy-mm-dd'
    });
    $( "#anim" ).change(function() {
      $( "#datepicker2" ).datepicker( "option", "showAnim", $( this ).val() );
    });

    $( "#datepicker3" ).datepicker({
      dateFormat: 'yy-mm-dd'
    });
    $( "#anim" ).change(function() {
      $( "#datepicker3" ).datepicker( "option", "showAnim", $( this ).val() );
    });

    $( "#datepicker4" ).datepicker({
      dateFormat: 'yy-mm-dd'
    });
    $( "#anim" ).change(function() {
      $( "#datepicker4" ).datepicker( "option", "showAnim", $( this ).val() );
    });

    
  });



  // Change date format for MySQL
    // Resource: http://jsfiddle.net/gaby/WArtA/
    // Resource: http://stackoverflow.com/questions/7500058/how-to-change-date-format-mm-dd-yy-to-yyyy-mm-dd-in-date-picker
    // $(function(){
    //       $("#end_date").datepicker({ dateFormat: 'yy-mm-dd' });
    //       $("#start_date").datepicker({ dateFormat: 'yy-mm-dd' }).bind("change",function(){
    //           var minValue = $(this).val();
    //           minValue = $.datepicker.parseDate("yy-mm-dd", minValue);
    //           minValue.setDate(minValue.getDate()+1);
    //           $("#end_date").datepicker( "option", "minDate", minValue );
    //       })
    //   });
