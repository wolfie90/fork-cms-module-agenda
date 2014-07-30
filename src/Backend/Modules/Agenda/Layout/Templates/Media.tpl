{include:{$BACKEND_CORE_PATH}/Layout/Templates/Head.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureStartModule.tpl}

<div class="pageTitle">
	<h2>{$item.title}: {$lblMedia|ucfirst}</h2>
</div>

<div class="tabs">
		<ul>
			<li><a href="#tabImages">{$lblImages|ucfirst}</a></li>
			<li><a href="#tabFiles">{$lblFiles|ucfirst}</a></li>
			<li><a href="#tabVideos">{$lblVideos|ucfirst}</a></li>
		</ul>
		
		<div id="tabImages">
			<div class="buttonHolderRight">
				<a href="{$var|geturl:'add_image'}&amp;agenda_id={$item.id}" class="button icon iconAdd" title="{$lblAddImage|ucfirst}">
					<span>{$lblAddImage|ucfirst}</span>
				</a>
			</div>
      
      <div class="seperator">&nbsp;</div>
      
      <div id="dataGridAgendaImagesHolder">
        {option:dataGridImages}
          <div class="dataGridImagesHolder">
            <form action="{$var|geturl:'mass_action'}" method="get" class="forkForms submitWithLink" id="massAction">
            <fieldset>
              <input type="hidden" name="agenda_id" value="{$item.id}" />
              {$dataGridImages}
            </fieldset>
            </form>
          </div>
        {/option:dataGridImages}
      </div>
      {option:!dataGridImages}<p>{$msgNoImages}</p>{/option:!dataGridImages}
		</div>
    
	<div id="tabFiles">
			<div class="buttonHolderRight">
				<a href="{$var|geturl:'add_file'}&amp;agenda_id={$item.id}" class="button icon iconAdd" title="{$lblAddFile|ucfirst}">
					<span>{$lblAddFile|ucfirst}</span>
				</a>
			</div>
      
      <div class="seperator">&nbsp;</div>
      
      <div id="dataGridAgendaFilesHolder">
        {option:dataGridFiles}
          <div class="dataGridFilesHolder">
            <form action="{$var|geturl:'mass_action'}" method="get" class="forkForms submitWithLink" id="massAction">
            <fieldset>
              <input type="hidden" name="agenda_id" value="{$item.id}" />
              {$dataGridFiles}
            </fieldset>
            </form>
          </div>
        {/option:dataGridFiles}
      </div>
      {option:!dataGridFiles}<p>{$msgNoFiles}</p>{/option:!dataGridFiles}
    </div>
    
    <div id="tabVideos">
			<div class="buttonHolderRight">
				<a href="{$var|geturl:'add_video'}&amp;agenda_id={$item.id}" class="button icon iconAdd" title="{$lblAddVideo|ucfirst}">
					<span>{$lblAddVideo|ucfirst}</span>
				</a>
			</div>
      
      <div class="seperator">&nbsp;</div>
      
      <div id="dataGridAgendaVideosHolder">
        {option:dataGridVideos}
          <div class="dataGridVideosHolder">
            <form action="{$var|geturl:'mass_action'}" method="get" class="forkForms submitWithLink" id="massAction">
            <fieldset>
              <input type="hidden" name="agenda_id" value="{$item.id}" />
              {$dataGridVideos}
            </fieldset>
            </form>
          </div>
        {/option:dataGridVideos}
      </div>
      {option:!dataGridVideos}<p>{$msgNoVideos}</p>{/option:!dataGridVideos}
    </div>
</div>

<div class="fullwidthOptions">
	<a href="{$var|geturl:'index'}" class="button">
		<span>{$lblBackToOverview|ucfirst}</span>
	</a>
</div>

{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureEndModule.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/Footer.tpl}