{include:{$BACKEND_CORE_PATH}/Layout/Templates/Head.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureStartModule.tpl}

<div class="pageTitle">
	<h2>{$lblAgenda|ucfirst}: {$lblEdit}</h2>
</div>

{form:edit}
	<label for="title">{$lblTitle|ucfirst}</label>
	{$txtTitle} {$txtTitleError}

	<div id="pageUrl">
		<div class="oneLiner">
			{option:detailURL}<p><span><a href="{$detailURL}/{$item.url}">{$detailURL}/<span id="generatedUrl">{$item.url}</span></a></span></p>{/option:detailURL}
			{option:!detailURL}<p class="infoMessage">{$errNoModuleLinked}</p>{/option:!detailURL}
		</div>
	</div>


	<div class="tabs">
		<ul>
			<li><a href="#tabContent">{$lblContent|ucfirst}</a></li>
			<li><a href="#tabLocation">{$lblLocation|ucfirst}</a></li>
			<li><a href="#tabSEO">{$lblSEO|ucfirst}</a></li>
		</ul>

		<div id="tabContent">
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td id="leftColumn">

						<div class="box">
							<div class="heading">
								<h3>
									<label for="body">{$lblText|ucfirst}</label>
								</h3>
							</div>
							<div class="optionsRTE">
								{$txtText} {$txtTextError}
							</div>
						</div>
						
						<div class="box">
							<div class="heading">
								<div class="oneLiner">
									<h3>
										<label for="introduction">{$lblSummary|ucfirst}</label>
									</h3>
								</div>
							</div>
							<div class="optionsRTE">
								{$txtIntroduction} {$txtIntroductionError}
							</div>
						</div>						

					</td>

					<td id="sidebar">

							<div class="box">
								<div class="heading">
									<h3>
										<label for="beginDateDate">{$lblDateAndTime|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
									</h3>
								</div>
								<div class="options">
									<p class="p0"><label for="startDate">{$lblBeginDate|ucfirst}</label></p>
									<div class="oneLiner">
										<p>
											{$txtBeginDateDate} {$txtBeginDateDateError}
										</p>
										<p>
											<label for="startTime">{$lblAt}</label>
										</p>
										<p>
											{$txtBeginDateTime} {$txtBeginDateTimeError}
										</p>
									</div>
								</div>
								<div id="endDateMain">
									<div class="options">
										<p class="p0"><label for="endDateDate">{$lblEndDate|ucfirst}</label></p>
										<div class="oneLiner">
											<p>
												{$txtEndDateDate} {$txtEndDateDateError}
											</p>
											<p>
												<label for="endTime">{$lblAt}</label>
											</p>
											<p>
												{$txtEndDateTime} {$txtEndDateTimeError}
											</p>
										</div>
									</div>
								</div>
								<div id="wholeDay">
									<div class="options">
										{$chkWholeDay} <label for="whole_day">{$lblWholeDay|ucfirst} </label> {$chkWholeDayError}
									</div>
								</div>
								<div class="options">
									<div id="recurringAgendaContainer">
										{$chkRecurring} <label for="recurring">{$lblRepeatAgenda|ucfirst} </label> {$chkRecurringError}
									</div>
									
									<!-- recurring agenda options -->
									<div id="recurringAgendaOptions">
										<div id="recurringTypeContainer" class="recurringOptions">
											<label for="type">{$lblType|ucfirst}:</label>
											{$ddmType} {$ddmTypeError}
										</div>
										
										<div id="recurringIntervalContainer" class="recurringOptions">
											<label for="interval">{$lblEvery|ucfirst}:</label>
											{$ddmInterval} {$ddmIntervalError} <label id="interval">{$lblDays|ucfirst}</label>
										</div>
										
											<!-- option showed based on type -->	 
											<div id="recurringDaysContainer" class="recurringOptions">
												<label for="recurringOn" class="recurringLabel">{$lblRepeat|ucfirst}:</label>
												<ul class="inputList">
												{iteration:days}
													<li>
														{$Days.chkDays}
														<label for="{$days.id}">{$days.label}</label>
													</li>
												{/iteration:days}
												</ul>
											</div> 
										
										<div id="recurringEndsContainer" class="recurringOptions">
											<label for="endsOn">{$lblEnds|ucfirst}:</label>
											<ul class="inputList">
												{iteration:ends_on}
													<li>
														{$ends_on.rbtEndsOn}
														<label for="{$ends_on.id}">{$ends_on.label}</label>
													</li>
												{/iteration:ends_on}
											</ul>
										
											<!-- options showed based on selected item of above -->
											<div id="recurringFrequencyContainer" class="recurringOptions">
												{$txtFrequency} {$txtFrequencyError}
												<label for="instances">{$lblTimes}</label>
											</div>
											
											<div id="recurringEnddateContainer" class="recurringOptions">
												<div class="oneLiner">
													<p>
													{$txtRecurrEndDateDate} {$txtRecurrEndDateDateError}
													</p>
													<p>
													<label for="endTime">{$lblAt}</label>
													</p>
													<p>
														{$txtRecurrEndDateTime} {$txtRecurrEndDateTimeError}
													</p>
												</div>
											</div>											
										</div>
									</div>
								</div>							
							</div>

							<div class="box">
								<div class="heading">
									<h3>
										<label for="categoryId">{$lblCategory|ucfirst}</label>
									</h3>
								</div>
								<div class="options">
									{$ddmCategoryId} {$ddmCategoryIdError}
								</div>
							</div>

							<div class="box">
								<div class="heading">
									<h3>
										{$lblSubscriptions|ucfirst}
									</h3>
								</div>
								<div class="options">
									<ul class="inputList">
										{iteration:subscriptions}
											<li>
												{$subscriptions.rbtSubscriptions}
												<label for="{$subscriptions.id}">{$subscriptions.label}</label>
											</li>
										{/iteration:subscriptions}
									</ul>
								</div>
							</div>
					</td>
				</tr>
			</table>
		</div>

		<div id="tabLocation">
			<div class="box">
				<div class="heading">
					<h3>
						<label for="location">{$lblLocation|ucfirst}</label>
					</h3>
				</div>
				<div class="horizontal">
					<div class="options">
						<label for="venue">{$lblName|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
						{$txtName} {$txtNameError}
					</div>
					<div class="options">
						<p>
							<label for="street">{$lblStreet|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
							{$txtStreet} {$txtStreetError}
						</p>
						<p>
							<label for="number">{$lblNumber|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
							{$txtNumber} {$txtNumberError}
						</p>
						<p>
							<label for="zip">{$lblZip|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
							{$txtZip} {$txtZipError}
						</p>
						<p>
							<label for="city">{$lblCity|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
							{$txtCity} {$txtCityError}
						</p>
						<p>
							<label for="country">{$lblCountry|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
							{$ddmCountry} {$ddmCountryError}
						</p>
					</div>
					<div class="options">
						{$chkGoogleMaps} <label for="googleMaps">{$lblGoogleMaps|ucfirst} </label> {$chkGoogleMapsError}
					</div>
				</div>
			</div>
		</div>
		
		<div id="tabSEO">
			{include:{$BACKEND_CORE_PATH}/layout/templates/seo.tpl}
		</div>
	</div>

	<div class="fullwidthOptions">
		<a href="{$var|geturl:'delete'}&amp;id={$item.id}" data-message-id="confirmDelete" class="askConfirmation button linkButton icon iconDelete">
			<span>{$lblDelete|ucfirst}</span>
		</a>
		<div class="buttonHolderRight">
			<input id="addButton" class="inputButton button mainButton" type="submit" name="add" value="{$lblSave|ucfirst}" />
		</div>
	</div>

	<div id="confirmDelete" title="{$lblDelete|ucfirst}?" style="display: none;">
		<p>
			{$msgConfirmDelete|sprintf:{$item.title}}
		</p>
	</div>
{/form:edit}

{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureEndModule.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/Footer.tpl}