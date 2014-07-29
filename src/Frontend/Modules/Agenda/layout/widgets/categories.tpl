{*
	variables that are available:
	- {$widgetAgendaCategories}:
*}

{option:widgetAgendaCategories}
	<section id="AgendaCategoriesWidget" class="mod">
		<div class="inner">
			<header class="hd">
				<h3>{$lblCategories|ucfirst}</h3>
			</header>
			<div class="bd content">
				<ul>
					{iteration:widgetAgendaCategories}
						<li>
							<a href="{$widgetAgendaCategories.url}">
								{$widgetAgendaCategories.label}&nbsp;({$widgetAgendaCategories.total})
							</a>
						</li>
					{/iteration:widgetAgendaCategories}
				</ul>
			</div>
		</div>
	</section>
{/option:widgetAgendaCategories}
