<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\jui\AutoComplete;
use kartik\money\MaskMoney;
use kartik\widgets\Select2;
use kartik\widgets\SwitchInput;
use kartik\datetime\DateTimePicker;

/* @var $this yii\web\View */
/* @var $model amilna\cap\models\Transaction */
/* @var $form yii\widgets\ActiveForm */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Transactions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

<div class="transaction-form">

    <?php $form = ActiveForm::begin(); ?>
	
						
	<div class="pull-right">
	<?php	
	/*		
		echo Yii::t('app','Only Template?');
		echo SwitchInput::widget([
			'name' => 'Transaction[template]',
			'type' => SwitchInput::CHECKBOX,						
		]);
	*/ 
	?>	
	</div>
		
	<div class="row"></div>
		
	<div class="well">
		<div class="row">		
			<div class="col-sm-3">
		<?/*= $form->field($model, 'time')->textInput() */?>
		<?= $form->field($model, 'time')->widget(DateTimePicker::classname(), [				
				'options' => ['placeholder' => 'Select operating time ...','disabled'=>true],
				'convertFormat' => true,
				'pluginOptions' => [
					'format' => 'yyyy-MM-dd HH:i:s',
					//'startDate' => '01-Mar-2014 12:00 AM',
					'todayHighlight' => true
				]
			]) 
		?>		    
			</div>
			<div class="col-sm-6">
		<?= $form->field($model, 'reference')->textInput(['maxlength' => 255,'disabled'=>true]) ?>		
			</div>
			<div class="col-sm-3">	
		<?= $form->field($model, 'type')->widget(Select2::classname(), [			
			'data' => $model->itemAlias('type'),				
			'options' => ['placeholder' => Yii::t('app','Select a transaction type...'),'disabled'=>true],
			'pluginOptions' => [
				'allowClear' => true
			],
			'pluginEvents' => [
				"change" => 'function() { 																						
								var tipe = $("#transaction-type").val();								
								filterOptions(tipe,"debet");
								filterOptions(tipe,"credit");
								
							}',
				/*			
				"select2-open" => 'function() { console.log("open"); }',
				"select2-opening" => 'function() { console.log("select2-opening"); }',
				"select2-close" => 'function() { console.log("close"); }',
				"select2-highlight" => 'function() { console.log("highlight"); }',
				"select2-removing" => 'function() { console.log("removing"); }',
				"select2-removed" => 'function() { console.log("removed"); }',
				"select2-loaded" => 'function() { console.log("loaded"); }',
				"select2-focus" => 'function() { console.log("focus"); }',
				"select2-blur" => 'function() { console.log("blur"); }',
				*/ 
			],
		]);?>
			</div>
		</div>
	</div>
	
	<div class="row">		
		<div class="col-sm-4">
	<?= $form->field($model, 'subject')->widget(AutoComplete::classname(), [							
			'clientOptions' => [
				'source' => Yii::$app->urlManager->createUrl("//cap/transaction/index?format=json&arraymap=subject"),					
			],
			'clientEvents' => [				
				'select' => 'function(event, ui) {												
								console.log(event,ui,"tes");							
							}',
			],
			'options'=>[
				'class'=>'form-control required','maxlength' => 255,
				'disabled'=>true				
			]
		]) 
	?>    
		</div>
		<div class="col-sm-8">	
	
	<?= $form->field($model, 'tags')->widget(Select2::classname(), [
		'options' => [
			'placeholder' => Yii::t('app','put additional tags ...'),
			'disabled'=>true
		],
		'pluginOptions' => [
			'tags' => $model::getTags(),
		],
	]) ?>
			
		</div>
	</div>	
	
	<div class="row">	
		<div class="col-sm-9">			
	<?= $form->field($model, 'title')->widget(AutoComplete::classname(), [			
			'clientOptions' => [
				'source' => Yii::$app->urlManager->createUrl("//cap/transaction/index?format=json&arraymap=title"),					
			],
			'clientEvents' => [				
				'select' => 'function(event, ui) {																			
								var id = ui.item.value;
								var url = "'.Yii::$app->urlManager->createUrl('//cap/transaction/template').'?id="+id;
								$.ajax(url, {
									success: function (xhr) {										
										xhr = JSON.parse(xhr);									
										if (xhr != null)
										{
											console.log(event,ui,xhr);								
											var t = JSON.parse(xhr.json);
											var total = $("#transaction-total").val();																						
											
											for (k in t)
											{
												$("#transaction-"+k).val(t[k]);
											}
											
											var select2_x = {"allowClear":true,"width":"resolve"};
											jQuery.when(jQuery("#transaction-type").select2(select2_x)).done(initSelect2Loading("transaction-type"));
											jQuery("#transaction-type").on("select2-open", function(){
												initSelect2DropStyle("#transaction-type");				
											});
											
											$(".detail").each(function(n,d){
												if ($(d).attr("id") != "detail_:N")
												{
													$(d).html("");												
												}												
											});
																						
												
											for (i in t.journals)
											{
												var j = t.journals[i];															
												j["amount"] = (j["ratio"]*total == 0?null:j["ratio"]*total);												
												renderFormDetails(j["type"] == 0?"debet":"credit",j["amount"],j);
												
												var n = 0;
												$(".detail").each(function(){
													var n0 = $(this).attr("id").replace("detail_","");
													if (n0 != ":N")
													{
														n = Math.max(n,parseInt(n0));
													}
												});
												
												$("#w2"+n).attr("data-ratio",j["ratio"]);
											}
											
										}										
									},
									error: function (xhr) {
										
									}
								});
								
							}',
			],
			'options'=>[
				'class'=>'form-control required','maxlength' => 255,	
				'disabled'=>true			
			]
		]) 
	?>    
		</div>
		<div class="col-sm-3">        			
    <?php		
		echo $form->field($model, 'total')->widget(MaskMoney::classname(), [								
				'pluginOptions' => [
					'prefix' => 'Rp ',
					'suffix' => '',
					'thousands' => '.',
					'decimal' => ',',
					'precision' => 2, 
					'allowNegative' => false,					
				],
				'options'=>['disabled'=>true]
			]);
			
		/*echo MaskedInput::widget([
						'name'=>"Transaction[credits][0][subtotal]",
						'mask' => '9[99].',
						'clientOptions'=>['repeat'=>3,'greedy'=>true]						
					]);	*/	
			
	?>						
		</div>		
	</div>	
	
	<div class="row">		
		<div class="col-sm-6">
			<div class="well debet">		
				<h4><?= Yii::t('app','Debet') ?> <small class="pull-right"><?= Yii::t('app','amount is automatically adjusted to total') ?></small></h4>
				<!--
				<div class="row">		
					<div class="col-xs-5"><h5><?= Yii::t('app','Account')?></h5></div>
					<div class="col-xs-3"><h5><?= Yii::t('app','Qty')?></h5></div>
					<div class="col-xs-4"><h5><?= Yii::t('app','Amount')?></h5></div>
				</div>		
				-->
			</div>
		</div>
		<div class="col-sm-6">
			<div class="well credit">	
				<h4><?= Yii::t('app','Credit') ?> <small class="pull-right"><?= Yii::t('app','amount is automatically adjusted to total') ?></small></h4>
				
			</div>
		</div>
	</div>
    
    <div class="row">		
		<div class="col-sm-12">
    <?= $form->field($model, 'remarks')->textarea(['rows' => 3,'disabled'=>true]) ?>
    <?php /*
    use vova07\imperavi\Widget;
    echo $form->field($model, 'remarks')->widget(Widget::className(), [
		'settings' => [
			'lang' => substr(Yii::$app->language,0,2),
			'minHeight' => 200,
			'imageUpload' => Url::to(['//cap/default/image-upload']),
			'imageManagerJson' => Url::to(['//cap/default/images-get']),			
			'fileUpload' => Url::to(['//cap/default/file-upload']),
			'fileManagerJson' => Url::to(['//cap/default/files-get']),
			'plugins' => [				
				'imagemanager',
				'filemanager',
				'video',
				'table',
				'clips',				
				'fullscreen'
			]
		]
	]);
    */?>
		</div>
	</div>
    
    <div class="form-group">
        <?/*= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) */?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<div id="template_form_details" class="hidden">
	<div id="detail_:N" class="detail">	
		<div class="row">
			<!--
			<div class="col-xs-2">
				<?= Yii::t('app','Account')?>
			</div>	-->
			<div class="col-xs-7" style="padding-right:0px;">																		
				<div class="kv-plugin-loading loading-w0:N">&nbsp;</div>				
				<?= Html::dropDownList("Transaction[:T][:N][account_id]",false,ArrayHelper::map($model->accounts(),"id","name"),["id"=>"w0:N","class"=>"form-control kv-hide input-md transaction-:T-account","placeholder"=>"Select an account...","style"=>"width:100%","data-krajee-select2"=>"select2_x","disabled"=>true]) ?>
			</div>	
			<div class="col-xs-5" style="padding-left:0px;">																						
				<?= Html::textInput("Transaction[:T][:N][remarks]",false,["id"=>"w3:N","class"=>"form-control","placeholder"=>"Remarks...","style"=>"width:100%","readonly"=>true]) ?>
			</div>
		</div>			
		<div class="row">
			<div class="col-xs-5" style="padding-right:0px;">  					
				<div class="input-group">
					<div class="input-group-addon"><?= Yii::t('app','Qty')?></div>
					<input type="text" id="w1:N-disp" class="form-control transaction-:T-quantity-disp" name="w1:N-disp" style="text-align:right;" readonly=true>
					<input type="hidden" id="w1:N" name="Transaction[:T][:N][quantity]" data-krajee-maskMoney="maskMoney_x">					
					<!--<input type="number" id="w1:N" class="form-control" name="Transaction[:T][:N][quantity]" step="0.1" min="0.1" style="text-align:right;padding-right:10px">-->
				</div>	
			</div>
			<div class="col-xs-7" style="padding-left:0px;">  					
				<div class="input-group">
					<div class="input-group-addon"><?= Yii::t('app','Rp')?></div>
					<input type="text" id="w2:N-disp" class="form-control transaction-:T-amount-disp" name="w2:N-disp" style="text-align:right" readonly=true>
					<input type="hidden" id="w2:N" class="transaction-:T-amount" name="Transaction[:T][:N][amount]" data-krajee-maskMoney="maskMoney_x">
					<input type="hidden" id="w4:N" class="transaction-:T-type" name="Transaction[:T][:N][type]" >
				</div>	
			</div>
		</div>
		<hr>	
	</div>	
</div>

<?php

$this->render('_script',['model'=>$model]);

