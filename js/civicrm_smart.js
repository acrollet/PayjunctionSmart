CRM.$(function($) {
  i = 0;
  window.setInterval(function(){
    CRM.api3("Contribution", "get", {
      "sequential": 1,
      "invoice_id": CRM.vars.payjunctionsmart.invoice_id
    }).done(function(result) {

      contribution_id = result.id;
      result_data = result.values[0];
      contact_id = result_data.contact_id;
      contribution_status = result_data.contribution_status_id;
      contribution_id = result_data.contribution_id;
      final_url = '/civicrm/contact/view/contribution?reset=1&action=view&id='+contribution_id+'&cid='+contact_id+'&context=dashboard';
      if(contribution_status == "2"){
        i++;
        if(i == "20"){
          window.location = final_url;
        }
      }else{
        window.location = final_url;
      }
    });
  },1000);
});
