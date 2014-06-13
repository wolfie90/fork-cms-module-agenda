{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
    <h2>{$lblAgenda|ucfirst}: {$msgEditSubscriptionOn|sprintf:{$itemTitle}}</h2>
    <div class="buttonHolderRight">
        <a href="{$SITE_URL}{$itemURL}" class="button icon iconZoom previewButton targetBlank">
            <span>{$lblView|ucfirst}</span>
        </a>
    </div>
</div>

{form:editSubscription}
    <div class="box">
        <div class="heading">
            <h3>{$lblSubscription|ucfirst}</h3>
        </div>
        <div class="options">
            <p>
                <label for="name">{$lblName|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
                {$txtName} {$txtNameError}
            </p>
            <p>
                <label for="email">{$lblEmail|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
                {$txtEmail} {$txtEmailError}
            </p>
        </div>
    </div>

    <div class="fullwidthOptions">
        <div class="buttonHolderRight">
            <input id="editButton" class="inputButton button mainButton" type="submit" name="edit" value="{$lblSave|ucfirst}" />
        </div>
    </div>
{/form:editSubscription}

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}