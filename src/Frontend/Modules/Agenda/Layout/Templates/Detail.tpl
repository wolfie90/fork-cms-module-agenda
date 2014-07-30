  {*	
	variables that are available:
	- {$item}: contains data about the agenda item
	- {$beginDate}: begindate of the agenda item
	- {$endDate} : enddate of the agenda item
	- {$images}: agenda item images
	- {$videos}: agenda item videos
	- {$files}: agenda item files
	- {$location}: location of the agenda item
	- {$locationSettings}: google maps settings
*}
  
<h1 itemprop="name">{$item.title}</h1>

<article  {option:item.color}class="{$item.color}"{/option:item.color}>
    <p class="meta">
      {* Category*}
      {$lblCategory|ucfirst}: <a href="{$item.category_full_url}" title="{$item.category_title}">{$item.category_title}</a>
    </p>

    <p>
        <b>{$lblDate|ucfirst}:</b>
        {$beginDate|date:{$dateFormatLong}:{$LANGUAGE}}
        {$beginDate|date:{$timeFormat}:{$LANGUAGE}}
        -
        {$endDate|date:{$dateFormatLong}:{$LANGUAGE}}
        {$endDate|date:{$timeFormat}:{$LANGUAGE}}
    </p>

    {* Agenda Single Image *}
    {*<div class="image">*}
        {*{option:item.image}<img src="{$item.image}" alt="{$item.title}" itemprop="image" />{/option:item.image}*}
    {*</div>*}

    {* Show location via Google Maps *}
    {option:googlemaps}
        {* @remark: do not remove the parseMap-class, it is used by JS *}
        <div id="map{$location.id}" class="parseMap" style="height: {$locationSettings.height}px; width: {$locationSettings.width}px;"></div>

        {option:locationSettings.directions}
            <aside id="locationSearch{$location.id}" class="locationSearch">
                <form method="get" action="#">
                    <p>
                        <label for="locationSearchAddress{$location.id}">{$lblStart|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
                        <input type="text" id="locationSearchAddress{$location.id}" name="locationSearchAddress" class="inputText" />
                        <span id="locationSearchError{$location.id}" class="formError inlineError" style="display: none;">{$errFieldIsRequired|ucfirst}</span>
                    </p>
                    <p>
                        <input type="submit" id="locationSearchRequest{$location.id}" name="locationSearchRequest" class="inputSubmit" value="{$lblShowDirections|ucfirst}" />
                    </p>
                </form>
            </aside>
        {/option:locationSettings.directions}

        {option:locationSettings.full_url}
            <p><a href="{$locationSettings.maps_url}" title="{$lblViewLargeMap}">{$lblViewLargeMap|ucfirst}</a></p>
        {/option:locationSettings.full_url}

        <div id="markerText{$location.id}" style="display: none;">
            <address>
                {$location.street} {$location.number}<br />
                {$location.zip} {$location.city}
            </address>
        </div>
    {/option:googlemaps}

    {* Show location info *}
    {option:location}
        <h2><b>{$lblLocation|ucfirst}:</b></h2>
        <div class="location">
             <p><b>     {option:location.name}{$lblName|ucfirst}: {$location.name}{/option:location.name} <br/>
                        {option:location.street}{$lblStreet|ucfirst}: {$location.street}{/option:location.street} <br />
                        {option:location.number}{$lblAddressNumber|ucfirst}: {$location.number}{/option:location.number}<br />
                        {option:location.zip}{$lblPostalCode|ucfirst}: {$location.zip}{/option:location.zip} <br />
                        {option:location.city}{$lblCity|ucfirst}: {$location.city}{/option:location.city}</b></p>
        </div>
    {/option:location}

    {* Agenda Multiple Images *}
    {option:images}
        <div class="images">
                <h2><b>{$lblImages|ucfirst}:</b></h2>
                <ul>
                        {iteration:images}
                        <li>
                            <img src="{$images.image_first}" alt="{$images.title}" title="{$images.title}" />
                            {*<img src="{$images.image_second}" alt="{$images.title}" title="{$images.title}" />*}
                            {*<img src="{$images.image_third}" alt="{$images.title}" title="{$images.title}" />*}
                        </li>
                        {/iteration:images}
                </ul>
        </div>
    {/option:images}
    
    {* Agenda Multiple Videos *}
    {option:videos}
        <div class="videos">
            <h2><b>{$lblVideos|ucfirst}:</b></h2>
            <ul>
            {iteration:videos}
                    <li><a class="fancybox fancybox.iframe" rel="gallery" href="{$videos.url}">
                            <img src="{$videos.image}" alt="{$videos.title}" title="{$videos.title}">
                    </a></li>
            {/iteration:videos}
            </ul>
        </div>
    {/option:videos}
    
    {* Agenda Multiple Files *}
    {option:files}
        <div class="files">
                <h2><b>{$lblFiles|ucfirst}:</b></h2>
                <ul>
                {iteration:files}
                        <li><a href="{$files.url}">{$files.title}</a></li>
                {/iteration:files}
                </ul>
        </div>
    {/option:files}

    {option:item.text}
        <div class="text">
            <h2><b>{$lblInformation|ucfirst}:</b></h2>
            {$item.text}
        </div>
    {/option:item.text}

    {option:item.allow_subscriptions}
        <section id="agendaSubscriptionForm" class="mod">
            <div class="inner">
                <header class="hd">
                    <h3 id="{$actSubscribe}">{$msgSubscribe|ucfirst}</h3>
                </header>
                <div class="bd">
                    {option:subscriptionIsInModeration}<div class="message warning"><p>{$msgAgendaSubscriptionInModeration}</p></div>{/option:subscriptionIsInModeration}
                    {option:subscriptionIsSpam}<div class="message error"><p>{$msgAgendaSubscriptionIsSpam}</p></div>{/option:subscriptionIsSpam}
                    {option:subscriptionIsAdded}<div class="message success"><p>{$msgAgendaSubscriptionIsAdded}</p></div>{/option:subscriptionIsAdded}
                    {form:subscriptionsForm}
                        <div class="alignBlocks">
                            <p {option:txtNameError}class="errorArea"{/option:txtNameError}>
                                <label for="name">{$lblName|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
                                {$txtName} {$txtNameError}
                            </p>
                            <p {option:txtEmailError}class="errorArea"{/option:txtEmailError}>
                                <label for="email">{$lblEmail|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
                                {$txtEmail} {$txtEmailError}
                            </p>
                        </div>
                        <p>
                            <input class="inputSubmit" type="submit" name="subscription" value="{$msgSubscribe|ucfirst}" />
                        </p>
                    {/form:subscriptionsForm}
                </div>
            </div>
        </section>
    {/option:item.allow_subscriptions}
</article>
