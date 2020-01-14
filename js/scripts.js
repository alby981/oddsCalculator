jQuery(window).ready(function ($) {
    
    var sport = "americanfootball_ncaaf";
    var mkt =  "h2h";
    var region =  "us";

    var oTable = $('#odds_guidelines').DataTable({
        "ajax": ajax_object.ajaxurl+"?action=getRemote&type=odds&sport="+sport+"&mkt="+mkt+"&region="+region,
        "columns": [
            { "data" : "sport_nice" },
            { "data" : "teams1" },
            { "data" : "teams2" },
            { "data" : "site_nice" },
            { "data" : "odds1" },
            { "data" : "odds2" },
        ],
        responsive:true
    });
//    new $.fn.dataTable.FixedHeader( oTable );
    
    
    function ajaxCall(action, type){
        $.ajax({
          url: ajax_object.ajaxurl,
          data: {action: action,odds_nonce: ajax_object.odds_nonce,type:type},
          type: 'GET',                                  
          success: function(response){    
              var data = JSON.parse(response);
              if(action == 'getChoices') {
                  var options = "<option value=''></option>";
                  for (key in data) {
                      if (data.hasOwnProperty(key)) {
                          options += "<option value='"+key+"'>" +data[key]+ "</option>";              
                      }
                  }
                  $("#odds_"+ type + "s").html(options);    
                  $("#odds_"+ type + "s").show();
              }
              if(action == 'getLatest') {
                  var html = '';
                  var change = false;
                  var bgcolor = '#dadada';
                  for(var i=0;i < data.length;i++) {
                      if ((typeof data[i+1] != "undefined"
                              && data[i+1].id != data[i].id)
                              || typeof data[i+1] == "undefined"
                              ) {
                            change = true;
                           
                      }
                      html += '<div class="row" style="background-color:' + bgcolor + '">';
                      html += '<div class="col-md-3 col-3 odds_latest-cell">' + data[i].odds + '</div>';
                      html += '<div class="col-md-3 col-3 odds_latest-cell">' + data[i].stake + '</div>';
                      html += '<div class="col-md-3 col-3 odds_latest-cell">' + data[i].format + '</div>';
                      if (change) {
                          html += '<div class="col-md-3 col-3 odds_latest-cell">$' + data[i].total_payout + '</div>';
                          if(bgcolor != '#dadada') {
                              bgcolor = '#dadada';
                          } else {
                            bgcolor = '#ccc';
                          }
                      } else {
                          html += '<div class="col-md-3 col-3 odds_latest-cell"></div>'
                      }
                      html += '</div>'
                      change = false;
                      //html += data[i].
                  }
                  $("#odds_latest").html(html)
              }
              
          }
      });
    }
    
        
    function getRemoteOdds(){
        $(".select_show_error").hide()
        var sport = $("#odds_sports").val();
        var mkt =  $("#odds_mkts").val();
        var region =  $("#odds_regions").val();
        if((typeof sport == 'undefined' || sport == null || sport == '') 
            || (typeof mkt == 'undefined' || mkt == null || mkt == '')
            || (typeof region == 'undefined' || region == null || region == '')
        ) {
            $(".select_show_error").show()
            $(".select_show_error").html("you need to select all the options")
            return false;
        }
        oTable.ajax.url( ajax_object.ajaxurl+"?action=getRemote&type=odds&sport="+sport+"&mkt="+mkt+"&region="+region ).load();
        oTable.ajax.reload()
    }
    
    $(".odd_calculator").on('click', '.odd_add', function () {
        var odd_row = $(".odd_container:last");
        var $tableBody = $('.odd_container').find(".row:last");
        $trNew = $tableBody.clone();
        $(".odd_container").append($trNew)

    });
    $("#odds_form").on('change', '#odds_format', function () {
        getOdds();
    });
    
    $("#odd_calc").click(function(){
        getOdds();
        ajaxCall('getLatest');
    });
    
    $("#odds_remote").on('click','.odd_submit',function(){
        getRemoteOdds(oTable);
    });
    
    
    function getOdds() {
        var odds_format = $("#odds_format").val();
        $(".odds_error").hide()
        var odds_data = $('#odds_form').serialize();
        $.ajax({
            url: ajax_object.ajaxurl,
            data: {action: 'getOdds',odds_nonce: ajax_object.odds_nonce,odds_data:odds_data,odds_format:odds_format},
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.status === 'ok') {
                    $(".odds_payout").html(response.msg)

                } else {
                    $(".odds_error").show()
                    $(".odds_error").html(response.msg)
                }
            }
        });
    }
    ajaxCall('getChoices','region');
    ajaxCall('getChoices','mkt');
    ajaxCall('getChoices','sport');
    ajaxCall('getLatest');
    
   
});