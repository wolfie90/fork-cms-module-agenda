/**
 * Interaction for the agenda module
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
jsBackend.agenda =
{
	// constructor
	init: function()
	{
		// do meta
		if($('#title').length > 0) $('#title').doMeta();
		
		// init divs
		$recurringAgenda = $('#recurringAgendaContainer input[name=recurring]');
        $recurringAgendaOptions = $('#recurringAgendaOptions');
		$recurringType = $('#recurringTypeContainer');
		$recurringInterval = $('#recurringIntervalContainer');
		$recurringDays = $('#recurringDaysContainer');
		$recurringEnds = $('#recurringEndsContainer');
		$recurringFrequency = $('#recurringFrequencyContainer');
		$recurringEnddate = $('#recurringEnddateContainer');
		$recurringEveryType = $('#recurringEveryTypeContainer');
		$recurringBeginTimeLabel = $("label[for='startTime']");
		$recurringBeginTime = $("#beginDateTime");
		$recurringEndTimeLabel = $("label[for='endTime']");
		$recurringEndTime = $("#endDateTime");
		$interval = $('label#interval');
		$wholeDay = $('#wholeDay');
		$enddate = $('#endDateMain');
		
		jsBackend.agenda.controls.init();
		jsBackend.agenda.controls.setVisibilityRecurringOptions();
		
		// color picker for adding a category -----> move to category.js
		//$('input#color').simpleColorPicker({ showEffect: 'fade', hideEffect: 'slide' });		
	}
}

jsBackend.agenda.controls =
{
	// init, something like a constructor
	init: function()
	{		
		// hide recurring item options on start
		$recurringAgendaOptions.hide();
		$recurringDays.hide();
		$recurringFrequency.hide();
		$recurringEnddate.hide();
		
		// show recurring item options
        $recurringAgenda.on('change', function(e)
		{
			$recurringChecked = $('input[name=recurring]:checked').val();
			
			if ($recurringChecked == 'Y'){
				$('#recurringAgendaContainer').css("margin-bottom", "7px");
				$recurringAgendaOptions.show();
			} else {
				$('#recurringAgendaContainer').css("margin-bottom", "0px");
				$recurringAgendaOptions.hide();
			}
		});
		
		// bind change
		$recurringType.on('change', function(e)
		{				
			$recurringTypeValue = $('#recurringTypeContainer option:selected').val();
			
			switch($recurringTypeValue)
			{
				case '0':
					$recurringDays.hide();
					$recurringEveryType.html(jsBackend.locale.lbl('Days'));
					$interval.text(jsBackend.locale.lbl('Days'));
					break;
				case '1':
					$recurringDays.show();
					$recurringEveryType.html(jsBackend.locale.lbl('Weeks'));
					$interval.text(jsBackend.locale.lbl('Weeks'));
					break;
				case '2':
					$recurringDays.hide();
					$recurringEveryType.html(jsBackend.locale.lbl('Months'));
					$interval.text(jsBackend.locale.lbl('Months'));
					break;
				case '3':
					$recurringDays.hide();
					$recurringEveryType.html(jsBackend.locale.lbl('Years'));
					$interval.text(jsBackend.locale.lbl('Years'));
					break;
			}
		});
		
		// bind change
		$recurringEnds.on('change', function(e)
		{
			$recurringEndsChecked = $('input[name=ends_on]:checked').val();
			
			switch($recurringEndsChecked)
			{
				case '1':
					$recurringFrequency.show();
					$recurringEnddate.hide();
					break;
				case '2':
					$recurringEnddate.show();
					$recurringFrequency.hide();
					break;
				default:
					$recurringFrequency.hide();
					$recurringEnddate.hide();
					break;
			}
		});
		
		// show or hide time depending on whole day item
		$wholeDay.on('change', function(e)
		{
			$wholeDayChecked = $('input[name=whole_day]:checked').val();
					
			if ($wholeDayChecked == 'Y') {
				$recurringBeginTimeLabel.hide();
				$recurringBeginTime.hide();
				$recurringEndTimeLabel.hide();
				$recurringEndTime.hide();
			} else {
				$recurringBeginTimeLabel.show();
				$recurringBeginTime.show();
				$recurringEndTimeLabel.show();
				$recurringEndTime.show();
			}
		});


	},
	
	setVisibilityRecurringOptions: function()
	{
		$recurringAgendaChecked = $('#recurringAgendaContainer input[name=recurring]:checked').val();
		
		// if values are known
		if ($recurringAgendaChecked == 'Y') {
			$recurringAgendaOptions.show();
		
			$recurringTypeValue = $('#recurringTypeContainer option:selected').val();
			
			switch($recurringTypeValue)
			{
				case '0':
					$recurringDays.hide();
					$recurringEveryType.html(jsBackend.locale.lbl('Days'));
					$interval.text(jsBackend.locale.lbl('Days'));
					break;
				case '1':
					$recurringDays.show();
					$recurringEveryType.html(jsBackend.locale.lbl('Weeks'));
					$interval.text(jsBackend.locale.lbl('Weeks'));
					break;
				case '2':
					$recurringDays.hide();
					$recurringEveryType.html(jsBackend.locale.lbl('Months'));
					$interval.text(jsBackend.locale.lbl('Months'));
					break;
				case '3':
					$recurringDays.hide();
					$recurringEveryType.html(jsBackend.locale.lbl('Years'));
					$interval.text(jsBackend.locale.lbl('Years'));
					break;
			}
			
			$recurringEndsChecked = $('input[name=ends_on]:checked').val();
			
			switch($recurringEndsChecked)
			{
				case '1':
					$recurringFrequency.show();
					$recurringEnddate.hide();
					break;
				case '2':
					$recurringEnddate.show();
					$recurringFrequency.hide();
					break;
				default:
					$recurringFrequency.hide();
					$recurringEnddate.hide();
					break;
			}
			
			$wholeDayChecked = $('input[name=whole_day]:checked').val();
			
			if ($wholeDayChecked == 'Y') {
				$recurringBeginTimeLabel.hide();
				$recurringBeginTime.hide();
				$recurringEndTimeLabel.hide();
				$recurringEndTime.hide();
			} else {
				$recurringBeginTimeLabel.show();
				$recurringBeginTime.show();
				$recurringEndTimeLabel.show();
				$recurringEndTime.show();
			}
		}
	}
}	

$(jsBackend.agenda.init);
