{include:{$BACKEND_CORE_PATH}/Layout/Templates/Head.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureStartModule.tpl}

<div class="pageTitle">
	<h2>{$lblModuleSettings|ucfirst}: {$lblAgenda}</h2>
</div>

{form:settings}
	<div class="box horizontal">
		<div class="heading">
			<h3>{$lblImages|ucfirst} - {$lblDimension} 1</h3>
		</div>
		<div class="options">
			<label for="width1">{$lblWidth|ucfirst}</label>{$txtWidth1} {$txtWidth1Error} px
		</div>
		<div class="options">
			<label for="height1">{$lblHeight|ucfirst}</label>{$txtHeight1} {$txtHeight1Error} px
		</div>
		<div class="options">
			<ul class="inputList p0">
				<li><label for="allowEnlargment1">{$chkAllowEnlargment1} {$lblAllowEnlargment|ucfirst}</label></li>
			</ul>
		</div>
		<div class="options">
			<ul class="inputList p0">
				<li><label for="forceAspectRatio1">{$chkForceAspectRatio1} {$lblForceAspectRatio|ucfirst}</label></li>
			</ul>
		</div>
    </div>
  
	<div class="box horizontal">
		<div class="heading">
			<h3>{$lblImages|ucfirst} - {$lblDimension} 2</h3>
		</div>
		<div class="options">
			<label for="width2">{$lblWidth|ucfirst}</label>{$txtWidth2} {$txtWidth2Error} px
		</div>
		<div class="options">
			<label for="height2">{$lblHeight|ucfirst}</label>{$txtHeight2} {$txtHeight2Error} px
		</div>
		<div class="options">
			<ul class="inputList p0">
				<li><label for="allowEnlargment2">{$chkAllowEnlargment2} {$lblAllowEnlargment|ucfirst}</label></li>
			</ul>
		</div>
		<div class="options">
			<ul class="inputList p0">
				<li><label for="forceAspectRatio2">{$chkForceAspectRatio2} {$lblForceAspectRatio|ucfirst}</label></li>
			</ul>
		</div>
    </div>
    
	<div class="box horizontal">
		<div class="heading">
			<h3>{$lblImages|ucfirst} - {$lblDimension} 3</h3>
		</div>
		<div class="options">
			<label for="width3">{$lblWidth|ucfirst}</label>{$txtWidth3} {$txtWidth3Error} px
		</div>
		<div class="options">
			<label for="height3">{$lblHeight|ucfirst}</label>{$txtHeight3} {$txtHeight3Error} px
		</div>
		<div class="options">
			<ul class="inputList p0">
				<li><label for="allowEnlargment3">{$chkAllowEnlargment3} {$lblAllowEnlargment|ucfirst}</label></li>
			</ul>
		</div>
		<div class="options">
			<ul class="inputList p0">
				<li><label for="forceAspectRatio3">{$chkForceAspectRatio3} {$lblForceAspectRatio|ucfirst}</label></li>
			</ul>
		</div>
    </div>
	
	<div class="box horizontal">
		<div class="heading">
			<h3>{$lblCacheTimeout|ucfirst} ({$lblSeconds|ucfirst})</h3>
		</div>
		<div class="options">
			<label for="cacheTimeout">{$lblCacheTimeout|ucfirst}</label>{$txtCacheTimeout} {$txtCacheTimeoutError}
		</div>
	</div>

    <div class="box">
        <div class="heading">
            <h3>{$lblSubscriptions|ucfirst}</h3>
        </div>
        <div class="options">
            <ul class="inputList">
                <li><label for="allowSubscriptions">{$chkAllowSubscriptions} {$lblAllowSubscriptions|ucfirst}</label></li>
                <li><label for="moderation">{$chkModeration} {$lblEnableModeration|ucfirst}</label></li>
                <li><label for="notifyByEmailOnNewSubscriptionToModerate">{$chkNotifyByEmailOnNewSubscriptionToModerate} {$msgNotifyByEmailOnNewSubscriptionToModerate|ucfirst}</label></li>
                <li><label for="notifyByEmailOnNewSubscription">{$chkNotifyByEmailOnNewSubscription} {$msgNotifyByEmailOnNewSubscription|ucfirst}</label></li>
        </div>
    </div>

    <div class="box horizontal">
        <div class="heading">
            <h3>{$lblIndividualMap|ucfirst}</h3>
        </div>
        <div class="options">
            <p>
                <label for="zoomLevel">{$lblZoomLevel|ucfirst}</label>
                {$ddmZoomLevel} {$ddmZoomLevelError}
            </p>
        </div>
        <div class="options"{option:!godUser} style="display:none;"{/option:!godUser}>
            <p>
                <label for="width">{$lblWidth|ucfirst}</label>
                {$txtWidth} {$txtWidthError}
                <span class="helpTxt">
					{$msgWidthHelp|sprintf:300:800}
				</span>
            </p>
        </div>
        <div class="options"{option:!godUser} style="display:none;"{/option:!godUser}>
            <p>
                <label for="height">{$lblHeight|ucfirst}</label>
                {$txtHeight} {$txtHeightError}
                <span class="helpTxt">
					{$msgHeightHelp|sprintf:150}
				</span>
            </p>
        </div>
        <div class="options">
            <p>
                <label for="mapType">{$lblMapType|ucfirst}</label>
                {$ddmMapType} {$ddmMapTypeError}
            </p>
        </div>
    </div>

	<div class="fullwidthOptions">
		<div class="buttonHolderRight">
			<input id="save" class="inputButton button mainButton" type="submit" name="save" value="{$lblSave|ucfirst}" />
		</div>
	</div
{/form:settings}

{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureEndModule.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/Footer.tpl}