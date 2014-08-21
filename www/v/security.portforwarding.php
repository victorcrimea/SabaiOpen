<form id="fe">
<input type='hidden' id='pftable' name='pftable'>
<div class='pageTitle'>Security: Port Forwarding</div>

<div class='controlBox'>
  <span class='controlBoxTitle'>Port Forwarding</span>
  <div class='controlBoxContent'> 
    
    <table id='list' class='listTable clickable'></table>
    
    <input type='button' value='Add' id='add'>
    <input type='button' id="savebutton" name="savebutton" value='Save' onclick="PORTcall()">
    <input type='button' value='Cancel' onclick='cancelGateway();'><span id='messages'>&nbsp;</span>


    <div id='hideme'>
        <div class='centercolumncontainer'>
            <div class='middlecontainer'>
                <div id='hiddentext' value-'Please wait...' ></div>
                <br>
            </div>
        </div>
      </div>

    <div class="smallText">
      <br><b>Proto</b>- Which protocol (tcp or udp) to forward. </li>
      <br><b>VPN</b> - Forward ports through the normal internet connection (WAN) or through the tunnel (VPN), or both. Note that the Gateways feature may result in may result in undefined behavior when devices routed through an interface have ports forwarded through a different interface. Additionally, ports will only be forwarded through the VPN when the VPN service is active. </li>
      <br><b>Src Address</b>(optional) - Forward only if from this address. Ex: "1.2.3.4", "1.2.3.4 - 2.3.4.5", "1.2.3.0/24", "me.example.com". </li>
      <br><b>Ext Ports</b> - The port(s) to be forwarded, as seen from the WAN. Ex: "2345", "200,300", "200-300,400". </li>
      <br><b>Int Port</b>- The destination port inside the LAN. Only one port per entry is supported. </li>
      <br><b>Int Address</b>- The destination address inside the LAN. </li>
    </div>
</div>
</div>
<p>
        <div id='footer'>Copyright © 2014 Sabai Technology, LLC</div>
</p>
</form>

<script type='text/ecmascript'>
var hidden, hide,res;
var f = E('fe'); 
var hidden = E('hideme'); 
var hide = E('hiddentext');
var portforwarding;

function PORTcall(){ 
  hideUi("Adjusting Port Forwarding settings..."); 
//read the text values
var TableData=new Array();
$('#list tr').each(function(row, tr){
    TableData[row] = {
        "status" : $(tr).find('td:eq(0)').text()
        , "proto" : $(tr).find('td:eq(1)').text()
        , "vpn" : $(tr).find('td:eq(2)').text()
        , "ext" : $(tr).find('td:eq(3)').text()
        , "int" : $(tr).find('td:eq(4)').text()
        , "intaddr" : $(tr).find('td:eq(5)').text()
        , "description" : $(tr).find('td:eq(6)').text()
      }
});

TableData = $.toJSON(TableData);
//var json=JSON.parse(TableData);
//alert(TableData);
//$("#pftable").val(TableData);
var json=$.parseJSON(TableData);
$("#pftable").val(TableData);
// Pass the form values to the php file 
  $.post('php/portforwarding.php', $("#fe").serialize(), function(pass){
    res=$.parseJSON(pass);
// Detect if values have been passed back   
    if( res.rMessage != ""){
      PORTresp();
    };
      showUi();
});
// Important stops the page refreshing
  return false;
} 


function PORTresp(){ 
  msg(res.rMessage); 
  showUi(); 
  } 

  var lt =  $('#list').dataTable({
    'bPaginate': false,
    'bInfo': false,
    'stateSave': true,
    'bProcessing': true,
    'sAjaxSource': 'libs/ptrs/port_forwarding.json',
    'aoColumns': [
      { 'sTitle': 'On/Off',       'mData':'status',     'sClass':'statusDrop'},  
      { 'sTitle': 'Proto',        'mData':'protocol',   'sClass':'protoDrop' },
      { 'sTitle': 'VPN',          'mData':'gateway',    'sClass':'vpnDrop' },
      { 'sTitle': 'Src Address',  'mData':'src',        'sClass':'plainText'  },
      { 'sTitle': 'Ext Port',     'mData':'ext',        'sClass':'plainText'   }, 
      { 'sTitle': 'Int Port',     'mData':'int',        'sClass':'plainText' },
      { 'sTitle': 'Int Address',  'mData':'address',    'sClass':'plainText'  },
      { 'sTitle': 'Description',  'mData':'description','sClass':'plainText'  }
    ],

    'fnRowCallback': function(nRow, aData, iDisplayIndex, iDisplayIndexFull){
      $(nRow).find('.plainText').editable(
        function(value, settings){ return value; },
        {
          'onblur':'submit',
          'event': 'click',
          'placeholder' : 'Click to edit'
        }
      );

      $(nRow).find('.statusDrop').editable(
        function(value, settings){ return value; },
        {
        'data': " {'on':'On','off':'Off'}",
        'type':'select',
        'onblur':'submit',
        'event': 'click'
        }
      );

      $(nRow).find('.protoDrop').editable(
        function(value, settings){ return value; },
        {
        'data': " {'UDP':'UDP','TCP':'TCP', 'Both':'Both'}",
        'type':'select',
        'onblur':'submit',
        'event': 'click'
        }
      );

      $(nRow).find('.vpnDrop').editable(
        function(value, settings){ return value; },
        {
        'data': " {'LAN':'LAN', 'WAN':'WAN','VPN':'VPN'}",
        'type':'select',
        'onblur':'submit',
        'event': 'click'
        }
      );

    } /* end fnRowCallback*/
  }) /* end datatable*/


  $('#add').click( function (e) {
    e.preventDefault();
    lt.fnAddData(
      { 
      "status": 'Off', 
      "protocol": 'Both',
      "gateway": 'WAN',
      "src": null,
      "ext": null,
      "int": null,
      "address": null,
      "description": null 
      }
    );
  });

  function saveGateway(){
    toServer('Save this.');
  };

  // function toggleExplain(){

  //   $("#description").toggle();
  //   if( $("#toggleDesc").text()=="Show Description") {
  //     $("#description").show();
  //     $("#toggleDesc").text("Hide Description");
  //   } else {
  //     $("#description").hide();
  //     $("#toggleDesc").text("Show Description");
  //   }
  // }

</script>