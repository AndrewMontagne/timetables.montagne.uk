var pixelIncrement = 1;
var scrollMillisecond = 25;
var refreshScrollWait = 3000;
var refreshPageWait = 20000;
var scroll_status;
var notice_status;
var was_just_paused;
var was_just_paused_notice;
var scrollCounter;
var noticeCounter;
var scrollLineHeight;
var noticeLineHeight;
var page_number;

function init_scroll()
{
    var scrollers = new Array();
    scroll_status = new Array();
    var scroll;
    var indent;
    var counter = 0;
    was_just_paused = false;
    scrollCounter = 0;

    scroll = document.getElementById('scroll0');

    if (scroll != null)
    {
        scrollLineHeight = getLineHeight(scroll);
    }

    while (scroll != null)
    {
        scroll.scrollTop = 0;
        scrollers[counter] = 'scroll' + counter;
        if (scroll.scrollHeight > (scroll.scrollTop + scroll.offsetHeight))
        {
            scroll_status[counter] = 0;
        }
        else
        {
            scroll_status[counter] = -10;
        }

        if (scrollLineHeight > 0)
        {
            indent = document.getElementById('indent' + counter);
            indent.innerHTML = '';
            for (var i = 0; i < scroll.offsetHeight / scrollLineHeight; i++)
            {
                indent.innerHTML += '<BR>';
            }
        }

        counter++;
        scroll = document.getElementById('scroll' + counter);
    }

    if (scrollers.length > 0)
    {
        setTimeout(function(){do_scroll(scrollers)},refreshScrollWait);
    }
}

function getLineHeight(elem)
{
    var orig_text = elem.innerHTML;
    elem.innerHTML += '<br>x';
    var h0 = elem.scrollHeight;
    elem.innerHTML += '<br>x';
    var h1 = elem.scrollHeight;
    elem.innerHTML = orig_text;
    return h1-h0;
}

function do_scroll(scrollers)
{
    var scroll;
    var indent;

    for (i=0; i < scrollers.length; i++)
    {
        scroll = document.getElementById(scrollers[i]);
        indent = document.getElementById('indent' + i);
        if ((scroll != null) && (scroll_status[i] >= 0))
        {
            if (scroll_status[i] == 0)
            {
                if (scroll.scrollHeight > (scroll.scrollTop + scroll.offsetHeight))
                {
                    scroll.scrollTop += pixelIncrement;
                }
                if (scroll.scrollTop >= (scroll.scrollHeight - scroll.offsetHeight))
                {
                    scroll_status[i] = 10;
                }
            }

            if (was_just_paused && (scroll_status[i] == 10))
            {
                scroll.style.display='none';
                scroll.scrollTop = 1;
                scroll_status[i] = 11;
            }
            else if (scroll_status[i] == 19)
            {
                scroll.style.display='';
                scroll.scrollTop = 1;
                scroll_status[i] = 20;
            }
            else if ((scroll_status[i] > 10) && (scroll_status[i] < 20))
            {
                scroll_status[i]++;
            }
            else if (was_just_paused && (scroll_status[i] == 20))
            {
                scroll_status[i] = 0;
            }
        }
    }

    scrollCounter++;
    if (scrollCounter % scrollLineHeight < 1)
    {
        was_just_paused = true;
        setTimeout(function(){do_scroll(scrollers)},refreshScrollWait);
    }
    else
    {
        was_just_paused = false;
        setTimeout(function(){do_scroll(scrollers)},scrollMillisecond);
    }
}

function init_page()
{
    var pagers = new Array();
    scroll_status = new Array();
    var pager;
    scrollCounter = 1;

    pager = document.getElementById('special_notice');
    if (pager != null)
    {
        pager.scrollTop = 0;
        pagers[0] = 'special_notice';
        if (!scrollLineHeight)
        {
            scrollLineHeight = getLineHeight(pager);
        }
        scroll_status[0] = Math.ceil((pager.scrollHeight - 5) / pager.offsetHeight);
        pager.innerHTML += "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
        page_number = document.getElementById('page_number');
        page_number.innerHTML = "Page 1 of " + scroll_status[0];
    }



    if (pagers.length > 0)
    {
        setTimeout(function(){do_page(pagers)},refreshPageWait);
    }
}

function do_page(pagers)
{
    var pager;

    for (i=0; i < pagers.length; i++)
    {
        pager = document.getElementById(pagers[i]);

        if (pager != null)
        {
            if (scrollCounter >= 0)
            {
                if (scrollCounter < scroll_status[i])
                {
                    pager.scrollTop += pager.offsetHeight;
                }
                else
                {
                    scrollCounter = 0;
                    pager.scrollTop = 0;
                }
            }

            scrollCounter++;

            page_number.innerHTML = "Page " + scrollCounter + " of " + scroll_status[0];
        }
    }

    if (scrollCounter >= 0)
    {
        setTimeout(function(){do_page(pagers)},refreshPageWait);
    }
    else
    {
        was_just_paused = false;
        setTimeout(function(){do_page(pagers)},refreshScrollWait);
    }
}


function init_notice()
{
    var scrollers = new Array();
    notice_status = new Array();
    var scroll;
    var indent;
    var counter = 0;
    was_just_paused_notice = false;
    noticeCounter = 0;

    scroll = document.getElementById('special_notice');

    if (scroll != null)
    {
        noticeLineHeight = getLineHeight(scroll);
        scroll.scrollTop = 2;
        scrollers[counter] = 'special_notice';
        if (scroll.scrollHeight > (scroll.scrollTop + scroll.offsetHeight))
        {
            notice_status[counter] = 0;
        }
        else
        {
            notice_status[counter] = -10;
        }
    }

    if (scrollers.length > 0)
    {
        setTimeout(function(){do_notice(scrollers)},refreshScrollWait);
    }
}

function do_notice(scrollers)
{
    var scroll;

    for (i=0; i < scrollers.length; i++)
    {
        scroll = document.getElementById(scrollers[i]);
        if ((scroll != null) && (notice_status[i] >= 0))
        {
            if (notice_status[i] == 0)
            {
                if (scroll.scrollHeight > (scroll.scrollTop + scroll.offsetHeight))
                {
                    scroll.scrollTop += pixelIncrement;
                }
                if (scroll.scrollTop >= (scroll.scrollHeight - scroll.offsetHeight))
                {
                    notice_status[i] = 10;
                }
            }

            if (was_just_paused_notice && (notice_status[i] == 10))
            {
                scroll.style.visibility='hidden';
                scroll.scrollTop = 2;
                notice_status[i] = 11;
            }
            else if (notice_status[i] == 19)
            {
                scroll.style.visibility='visible';
                scroll.scrollTop = 2;
                notice_status[i] = 20;
            }
            else if ((notice_status[i] > 10) && (notice_status[i] < 20))
            {
                notice_status[i]++;
            }
            else if (was_just_paused_notice && (notice_status[i] == 20))
            {
                notice_status[i] = 0;
            }
        }
    }

    noticeCounter++;
    if (noticeCounter % noticeLineHeight < 1)
    {
        was_just_paused_notice = true;
        setTimeout(function(){do_notice(scrollers)},refreshScrollWait);
    }
    else
    {
        was_just_paused_notice = false;
        setTimeout(function(){do_notice(scrollers)},scrollMillisecond);
    }
}

function init_refresh()
{
    var currentDate;
    var currentSeconds;
    var currentMilliseconds;
    var countdown;
    var secondsInMinute = 60;
    var millisecondsInSecond = 1000;

    currentDate = new Date();
    currentSeconds = currentDate.getSeconds();
    currentMilliseconds = currentDate.getMilliseconds();
    countdown = secondsInMinute - Number(currentSeconds);
    setTimeout("location.reload(true);",Number((millisecondsInSecond * countdown) - currentMilliseconds + 1));
}