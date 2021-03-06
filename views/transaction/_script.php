<?php

use yii\helpers\Url;
?>
<script type="text/javascript">
<?php $this->beginBlock('JS_END') ?>
		var accounts = <?= json_encode($model->accounts()) ?>;
		var journals = <?= \yii\helpers\Json::encode($model->isNewRecord?$model->id:$model->journals) ?>;
		var usedaccounts = [];
		
		function filterOptions(tipe,increaseon)
		{			
			var inc = (increaseon == "debet"?0:0);
			if (tipe == 1)
			{
				var inc = (increaseon == "debet"?0:1);
			}
			else if (tipe == 2)
			{
				var inc = (increaseon == "debet"?0:0);			
			}												
			else if (tipe == 3)
			{
				var inc = (increaseon == "debet"?0:1);			
			}										
			else if (tipe == 4)
			{
				var inc = (increaseon == "debet"?1:0);			
			}					
			
			var ibl = (increaseon == "debet"?true:true);
			if (tipe == 1)
			{
				var ibl = (increaseon == "debet"?true:false);
			}
			else if (tipe == 2)
			{
				var ibl = (increaseon == "debet"?false:true);			
			}										
			else if (tipe == 3)
			{
				var ibl = (increaseon == "debet"?true:true);			
			}
			else if (tipe == 4)
			{
				var ibl = (increaseon == "debet"?true:true);			
			}
			
			$(".transaction-"+increaseon+"-account option").each(function(i,d){
				
				var dval = $(d).val();
				for (n in accounts) {
					var a = accounts[n];						
					if (a["id"]	== dval)
					{						
						var isuse = false;
						if ($(d).prop("selected"))
						{
							usedaccounts[a["id"]] = true;	
							isuse = true;
						}						
						
						//console.log(a,a["isbalance"],ibl);
						
						if (a["isbalance"] === ibl && a["increaseon"] === inc && ((typeof usedaccounts[a["id"]] == "undefined") || isuse) )
						{
							$(d).prop("disabled",false);
							$(d).attr("class","");
						}
						else
						{
							$(d).prop("disabled",true);
							$(d).attr("class","hidden");
							if ($(d).prop("selected"))
							{
								var p = $(d).parent();
								p.val(false);	
								var n = p.attr("id").replace("w0","");
								
								var select2_x = {"allowClear":true,"width":"resolve"};
								jQuery.when(jQuery("#w0"+n).select2(select2_x)).done(initSelect2Loading("w0"+n));
								jQuery("#w0"+n).on("select2-open", function(){
									initSelect2DropStyle("w0"+n);				
								});
								
								usedaccounts = [];	
							}
						}

					}
					
				}
			});
			
		}
		
		function renderFormDetails(increaseon,defval,journal)
		{	
			var xhr = $("#template_form_details").html();
														
			var n = $(".detail").length;
			
			$(".detail").each(function(){
				var n0 = $(this).attr("id").replace("detail_","");
				if (n0 != ":N")
				{
					n = Math.max(n,parseInt(n0));
				}
			});
			//xhr = xhr.replace(/w0/g,"w0:N").replace(/w1/g,"w1:N").replace(/w2/g,"w1:N").replace(/:N/g,n);
			xhr = xhr.replace(/:T/g,increaseon).replace(/:N/g,n);
			$("."+increaseon).append(xhr);						
			
			
			$("#w0"+n+"").val(false);
			if (typeof journal !== "undefined")
			{
				$("#w0"+n+"").val(typeof journal["account_id"] !== "undefined"?journal["account_id"]:false);
			}
			$("#w0"+n+"").unbind("change");
			$("#w0"+n+"").bind("change",function(){
				var tipe = $("#transaction-type").val();			
				filterOptions(tipe,"debet");
				filterOptions(tipe,"credit");															
			});
			
			var select2_x = {"allowClear":true,"width":"resolve"};			
			jQuery("#w0"+n).prepend("<option val></option>");
			jQuery.when(jQuery("#w0"+n).select2(select2_x)).done(initSelect2Loading("w0"+n));
			jQuery("#w0"+n).on("select2-open", function(){
				initSelect2DropStyle("w0"+n);				
			});						
			
			$("#w1"+n+"").val(1.0);
			if (typeof journal !== "undefined")
			{
				$("#w1"+n+"").val(typeof journal["quantity"] !== "undefined"?journal["quantity"]:1);
			}
			
			var maskMoney_x = {"prefix":"","suffix":"","thousands":".","decimal":",","precision":2,"allowNegative":false};			
			jQuery("#w1"+n+"-disp").maskMoney(maskMoney_x);
			var val = parseFloat(jQuery("#w1"+n).val());
			jQuery("#w1"+n+"-disp").maskMoney("mask", val);
			jQuery("#w1"+n+"-disp").on("change", function () {
				 var numDecimal = jQuery("#w1"+n+"-disp").maskMoney("unmasked")[0];
				jQuery("#w1"+n).val(numDecimal);
				jQuery("#w1"+n).trigger("change");
			});
			
			var total = (typeof defval !== "undefined"?defval:$("#transaction-total").val());
			
			$("#w2"+n+"").val(total);
			if (typeof journal !== "undefined")
			{
				$("#w2"+n+"").val(typeof journal["amount"] !== "undefined"?journal["amount"]:total);
			}
			
			var maskMoney_x = {"prefix":"","suffix":"","thousands":".","decimal":",","precision":2,"allowNegative":false};							
			jQuery("#w2"+n+"-disp").maskMoney(maskMoney_x);
			var val = parseFloat(jQuery("#w2"+n).val());			
			jQuery("#w2"+n+"-disp").maskMoney("mask", val);
			jQuery("#w2"+n+"-disp").on("change", function () {
				 var numDecimal = jQuery("#w2"+n+"-disp").maskMoney("unmasked")[0];				 
				jQuery("#w2"+n).val(numDecimal);				
				jQuery("#w2"+n).trigger("change");
			});								
						
			if (typeof journal !== "undefined")
			{
				$("#w3"+n+"").val(typeof journal["remarks"] !== "undefined"?journal["remarks"]:null);
			}
			
			$("#w4"+n+"").val(increaseon == "debet"?0:1);			
			
			$("#transaction-total,.transaction-debet-amount,.transaction-credit-amount").unbind("change");																
			
			$(".transaction-debet-amount").bind("change",function(){
				accountAmount("debet");								
			});
			
			$(".transaction-credit-amount").bind("change",function(){
				accountAmount("credit");								
			});	
			
			$("#transaction-total").bind("change",function(){
				accountAmount("debet",true);
				accountAmount("credit",true);
			});								
			
			var tipe = $("#transaction-type").val();			
			filterOptions(tipe,"debet");
			filterOptions(tipe,"credit");														
		}	
		
		function accountAmount(increaseon,istotal)
		{			
			var maxA= parseFloat($("#transaction-total").val());
			var dA = 0;
			var lA = 0;
			var lD = false;
			
			var dId = "";
			
			$(".transaction-"+increaseon+"-amount").each(function(){
				
				if (typeof istotal !== "undefined")
				{
					if (typeof $(this).attr("data-ratio") !== "undefined")
					{
						$(this).val($(this).attr("data-ratio")*maxA);	
					}					
				}				
				
				var A = parseFloat($(this).val());
				
				var nA = (dA+A > maxA?maxA-dA:A);
				$(this).val(nA);
				jQuery("#"+$(this).attr("id")+"-disp").maskMoney("mask", nA);
				
				dA += nA;
				//console.log(dA,maxA,A,nA);									
				
				lA = nA;
				lD = $(this).attr("id");
			});	
			
			$(".transaction-"+increaseon+"-amount").each(function(){				
				var A = parseFloat($(this).val());
				
				if (A == 0)
				{
					var id = $(this).attr("id").replace("w2","");
					dId += (dId == ""?"#":",#")+"detail_"+id;					
					//console.log($("#w0"+id).val());
					delete usedaccounts[parseInt($("#w0"+id).val())];						
					
				}								
			});																
											
			$(dId).css("display","none");
			$(dId).html("");
			
			if (dA < maxA)
			{
				renderFormDetails(increaseon,maxA-dA);
			}	
			
			var tipe = $("#transaction-type").val();			
			filterOptions(tipe,increaseon);			
		}
		
		$("#transaction-total").bind("change",function(){
			accountAmount("debet",true);
			accountAmount("credit",true);
		});
		
		for (i in journals)
		{
			var j = journals[i];								
			renderFormDetails(j["type"] == 0?"debet":"credit",j["amount"],j);
		}
				
		
		
<?php $this->endBlock(); ?>


<?php $this->beginBlock('JS_READY') ?>
   
<?php $this->endBlock(); ?>

</script>
<?php
yii\web\YiiAsset::register($this);
$this->registerJs($this->blocks['JS_END'], yii\web\View::POS_END);
$this->registerJs($this->blocks['JS_READY'], yii\web\View::POS_READY);
