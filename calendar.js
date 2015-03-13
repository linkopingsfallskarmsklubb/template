
//************************************************************************************
// Draw calendar
//************************************************************************************

Date.prototype.getWeek = function() {
  var onejan = new Date(this.getFullYear(),0,1);
  return Math.ceil((((this - onejan) / 86400000) + onejan.getDay()+1)/7);
}

function cal_draw_f(node, buttons, date, select_cb, highlight_cb, navigate_cb) {
  var now = new Date();
  var today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
  if (date == undefined) {
    date = today;
  }
  var year    = date.getFullYear();
  var month   = date.getMonth();
  var p_year  = new Date(year, month-1,1).getFullYear();
  var p_month = new Date(year, month-1,1).getMonth();

  // Insert the calendar into html placeholder
  var html = "<div class='calendar_title'>" +
    node.getAttribute('data-title') + "</div>";

  if (buttons) {
    html +=  "<div class='calendar_nav'> \
                <div class='calendar_nav_left'> \
                  <div class='calendar_nav_img_cent'>&#x21e6;</div> \
                </div> \
                <div class='calendar_nav_date'> \
                </div> \
                <div class='calendar_nav_right'> \
                  <div class='calendar_nav_img_cent'>&#x21e8;</div> \
                </div>";
  }

  html +=    "<div class='calendar_wd'></div> \
              <div class='calendar_wk'></div> \
              <div class='calendar_cl'></div>";

  node.innerHTML = html;

  if (buttons) {
    var nodeLeft = node.getElementsByClassName('calendar_nav_left')[0];
    var nodeRight = node.getElementsByClassName('calendar_nav_right')[0];

    nodeLeft.onclick = function() {
      navigate_cb(new Date(year, month-1, 1));
    };
    nodeRight.onclick = function() {
      navigate_cb(new Date(year, month+1, 1));
    }
  }

  // Numeric month to long month
  var longMonth     = new Array(12);
      longMonth[0]  = "Januari";
      longMonth[1]  = "Februari";
      longMonth[2]  = "Mars";
      longMonth[3]  = "April";
      longMonth[4]  = "Maj";
      longMonth[5]  = "Juni";
      longMonth[6]  = "Juli";
      longMonth[7]  = "Augusti";
      longMonth[8]  = "September";
      longMonth[9]  = "Oktober";
      longMonth[10] = "November";
      longMonth[11] = "December";



  // Number of days in current month
  function daysInMonth(iMonth, iYear) {
    return 32 - new Date(iYear, iMonth, 32).getDate();
  }
  var monthDays = daysInMonth(month, year);


  // Number of days in previous month
  function daysInMonth(iMonth, iYear) {
    return 32 - new Date(iYear, iMonth, 32).getDate();
  }
  var p_monthDays = daysInMonth(p_month,p_year);

  // Last Monday of previous month
  var first_wd = new Date(year,month,01).getDay();
  if (first_wd == 0) {first_wd = 7;} // sunday=0,
  var extra_days      = first_wd -1;
  var start_month_day = p_monthDays - extra_days +1;


  //-----------------------------------------
  // Define three different html strings
  //-----------------------------------------

  var html_wd = "";
  var html_wk = "";


  //-----------------------------------------
  // Weekday header
  //-----------------------------------------

  html_wd += "<div class='wd'>Må</div> \
              <div class='wd'>Ti</div> \
              <div class='wd'>On</div> \
              <div class='wd'>To</div> \
              <div class='wd'>Fr</div> \
              <div class='wd'>Lö</div> \
              <div class='wd'>Sö</div>";



  //-----------------------------------------
  // Previous month
  //-----------------------------------------

  // Container around dates (adds top/left border)
  var html_cl = document.createElement("div");
  html_cl.className = 'cont_cell';

  var mnp = month;
  if (mnp < 10) {mnp = "0" + mnp; }

  if (extra_days > 0) {

    for (var i=start_month_day; i<=p_monthDays; i++) {

      // Current week day
      var d = new Date(p_year, p_month, i);

      var div = document.createElement("div");
      if (d.getDay() == 1) {
        var weekdate = new Date(p_year, p_month, i);
        var weeknr   = weekdate.getWeek();
        html_wk += "<div class='wk'>v." + weeknr + "</div>";
        div.className = "cell other md";
        div.innerHTML = "<div class='cell_wrapper'> \
                          <div class='innercell'>" + i + "</div> \
                        </div>";
      }
      else {
        div.className = "cell other";
        div.innerHTML = "<div class='cell_wrapper'> \
                           <div class='innercell'>" + i + "</div> \
                         </div>";
      }
      html_cl.appendChild(div);
    }
  }


  //-----------------------------------------
  // Current month
  //-----------------------------------------
  for (var i=1; i<=monthDays; i++) {

    // Pad day and month string
    var wd = i;
    var mn = month +1;
    if (wd < 10) {wd = "0" + wd; }
    if (mn < 10) {mn = "0" + mn; }

    // Current week day
    var d = new Date(year, month, wd);

    var classes = "";
    if (d - today == 0) {
      classes += " cur_dat";
    }
    if (highlight_cb != undefined) {
      classes += " " + highlight_cb(d);
    }

    // Different handling for Mondays
    var div = document.createElement("div");
    if (d.getDay() == 1) {
      var weeknr   = d.getWeek();
      html_wk += "<div class='wk'>v." + weeknr + "</div>";
      div.className = "cell md" + classes;
      div.innerHTML = "<div class='cell_wrapper'> \
                         <div class='innercell'>" + i + "</div> \
                       </div>";
    }
    else {
      div.className = "cell" + classes;
      div.innerHTML = "<div class='cell_wrapper'> \
                         <div class='innercell'>" + i + "</div> \
                       </div>";
    }
    div.onclick = function(d, div) {
      return function() {
        jQuery('.sel_date').removeClass('sel_date');
        div.className += ' sel_date';
        if (select_cb(d) === false) {
          jQuery('.sel_date').removeClass('sel_date');
        }
      }
    } (d, div);
    html_cl.appendChild(div);
  }

  //-----------------------------------------
  // Next month
  //-----------------------------------------

  var d = new Date(year, month, wd);

  var mnn = month +2;
  if (mnn < 10) {mnn = "0" + mnn; }

  if (d.getDay() != 0) {
    var wd = 1;
    for (var i=d.getDay()+1; i<=7; i++) {
      var wdl = wd;
      if (wdl < 10) {wdl = "0" + wdl; }
      var div = document.createElement("div");
      div.className = "cell other";
      div.innerHTML = "<div class='cell_wrapper'> \
                         <div class='innercell'>" + wd + "</div> \
                       </div>";
      wd++;
      html_cl.appendChild(div);
    }
  }

  //-----------------------------------------
  // Put all html into divs
  //-----------------------------------------
  if (buttons) {
    node.getElementsByClassName('calendar_nav_date')[0].innerHTML = "<div class='calendar_nav_date_cent'><b>" + longMonth[month] + "<br>" + year + "</b></div>";
  }
  node.getElementsByClassName('calendar_wd')[0].innerHTML       = html_wd;
  node.getElementsByClassName('calendar_wk')[0].innerHTML       = html_wk;
  node.getElementsByClassName('calendar_cl')[0].appendChild(html_cl);
}

function date2iso(d) {
  var fd = d.getFullYear() + '-';
  if (d.getMonth() < 10) { fd += "0"; }
  fd += (d.getMonth()+1) + '-';
  if (d.getDate() < 10) { fd += "0"; }
  fd += d.getDate();
  return fd;
}
function iso2date(d) {
  if (d == undefined) {
    return undefined;
  }
  var year = d.substring(0, 4);
  var month = parseInt(d.substring(5, 7)) - 1;
  var date = d.substring(8, 10);
  return new Date(year, month, date);
}

