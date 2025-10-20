(function(){
    'use strict';

    function ready(fn){
        if(document.readyState!=='loading'){fn();}else{document.addEventListener('DOMContentLoaded',fn)}
    }

    ready(function(){
        const container=document.querySelector('.lunar-calendar-container');
        if(!container) return;

        // Expect moment, vi locale and window._calendar from @nghiavuive/lunar_date_vi
        if(typeof moment==='undefined'){console.warn('moment.js not loaded');return;}
        if(moment && moment.locale){moment.locale('vi');}

        // Minimal runtime that delegates main logic to the original template behavior
        // Using a simplified class to rebuild calendar days and wire controls

        const dayNames=['Chủ nhật','Thứ hai','Thứ ba','Thứ tư','Thứ năm','Thứ sáu','Chủ nhật'];

        const state={
            currentDate: moment(),
            selectedDate: moment(),
            isNavigating:false
        };

        const el={
            gregorianDay: document.getElementById('current-gregorian-day'),
            gregorianMonthYear: document.getElementById('current-gregorian-month-year'),
            gregorianDayName: document.getElementById('current-gregorian-day-name'),
            lunarDay: document.getElementById('current-lunar-day'),
            lunarMonthYear: document.getElementById('current-lunar-month-year'),
            lunarDetails: document.getElementById('current-lunar-details'),
            monthYear: document.getElementById('current-month-year'),
            monthSelector: document.getElementById('month-selector'),
            yearSelector: document.getElementById('year-selector'),
            viewBtn: document.getElementById('view-btn'),
            todayBtn: document.getElementById('today-btn'),
            days: document.getElementById('calendar-days')
        };

        function getLunarDate(mDate){
            try{
                if(window._calendar && window._calendar.SolarDate){
                    const d=mDate.date();
                    const m=mDate.month()+1;
                    const y=mDate.year();
                    const solar=new window._calendar.SolarDate({day:d,month:m,year:y});
                    const lunar=solar.toLunarDate();
                    lunar.init && lunar.init();
                    return {day:lunar.day,month:lunar.month,yearName:lunar.getYearName?lunar.getYearName():'',dayName:lunar.getDayName?lunar.getDayName():'',monthName:lunar.getMonthName?lunar.getMonthName():''};
                }
            }catch(e){console.warn('lunar calc failed',e)}
            return {day:'',month:'',yearName:'',dayName:'',monthName:''};
        }

        function updateHeader(){
            const d=state.selectedDate;
            const gDay=d.date();
            const gMonth=d.month()+1;
            const gYear=d.year();
            const lunar=getLunarDate(d);
            if(el.gregorianDay) el.gregorianDay.textContent=String(gDay).padStart(2,'0');
            if(el.gregorianMonthYear) el.gregorianMonthYear.textContent=`Tháng ${String(gMonth).padStart(2,'0')} năm ${gYear}`;
            if(el.gregorianDayName) el.gregorianDayName.textContent=dayNames[d.day()];
            if(el.lunarDay) el.lunarDay.textContent=lunar.day;
            if(el.lunarMonthYear) el.lunarMonthYear.textContent=`Tháng ${lunar.month} năm ${lunar.yearName}`;
            if(el.lunarDetails) el.lunarDetails.textContent=`Ngày ${lunar.dayName} - Tháng ${lunar.monthName}`;
            if(el.monthYear) el.monthYear.textContent=`Tháng ${state.currentDate.month()+1} - ${state.currentDate.year()}`;
            if(el.monthSelector) el.monthSelector.value=state.currentDate.month()+1;
            if(el.yearSelector) el.yearSelector.value=state.currentDate.year();
        }

        function generateCalendar(){
            if(!el.days) return;
            el.days.innerHTML='';
            const start=state.currentDate.clone().startOf('month');
            const end=state.currentDate.clone().endOf('month');
            const startCal=start.clone().startOf('week');
            const endCal=end.clone().endOf('week');
            const cur=startCal.clone();
            while(cur.isSameOrBefore(endCal,'day')){
                const div=document.createElement('div');
                div.className='lunar-calendar-day';
                if(!cur.isSame(state.currentDate,'month')) div.classList.add('other-month');
                if(cur.isSame(state.selectedDate,'day')) div.classList.add('selected');
                if(cur.isSame(moment(),'day')) div.classList.add('today');
                const dn=document.createElement('div'); dn.className='lunar-day-number'; dn.textContent=cur.date(); div.appendChild(dn);
                const ld=document.createElement('div'); ld.className='lunar-lunar-day'; ld.textContent=getLunarDate(cur).day; div.appendChild(ld);
                div.addEventListener('click',function(){ if(div.classList.contains('other-month')) return; state.selectedDate=cur.clone(); updateHeader(); generateCalendar(); });
                el.days.appendChild(div);
                cur.add(1,'day');
            }
        }

        function bindEvents(){
            const prevMonth=document.getElementById('prev-month');
            const nextMonth=document.getElementById('next-month');
            const prevDay=document.getElementById('prev-day-btn');
            const nextDay=document.getElementById('next-day-btn');

            prevMonth && prevMonth.addEventListener('click',()=>{state.currentDate=state.currentDate.clone().subtract(1,'month');updateHeader();generateCalendar();});
            nextMonth && nextMonth.addEventListener('click',()=>{state.currentDate=state.currentDate.clone().add(1,'month');updateHeader();generateCalendar();});

            prevDay && prevDay.addEventListener('click',()=>{state.selectedDate=state.selectedDate.clone().subtract(1,'day');state.currentDate=state.selectedDate.clone();updateHeader();generateCalendar();});
            nextDay && nextDay.addEventListener('click',()=>{state.selectedDate=state.selectedDate.clone().add(1,'day');state.currentDate=state.selectedDate.clone();updateHeader();generateCalendar();});

            el.viewBtn && el.viewBtn.addEventListener('click',()=>{
                const m=parseInt(el.monthSelector.value,10); const y=parseInt(el.yearSelector.value,10);
                state.currentDate=moment([y,m-1]); updateHeader(); generateCalendar();
            });
            el.todayBtn && el.todayBtn.addEventListener('click',()=>{state.currentDate=moment(); state.selectedDate=moment(); updateHeader(); generateCalendar();});
            window.addEventListener('resize',()=>{ generateCalendar(); });
        }

        updateHeader();
        generateCalendar();
        bindEvents();
    });
})();

