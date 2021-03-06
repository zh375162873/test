/* *
 * 给定一个剩余时间（s）动态显示一个剩余时间.
 * 当大于一天时。只显示还剩几天。小于一天时显示剩余多少小时，多少分钟，多少秒。秒数每秒减1 *
 */

// 初始化变量
var auctionDate = 0;
var _GMTEndTime = 0;
var _day = '天';
var _hour = '小时';
var _minute = '分钟';
var _second = '秒';
var _end = '结束';
var cur_date = new Date();
var startTime = cur_date.getTime();
var Temp;
var TempStartStr = "";
var TempEndStr = "";
var leftTimerID = null;
var timerRunning = false;
var leftTimeDOM = null;
var leftTimeCallBack;

function showtime() {
    var now = new Date();
    var ts = parseInt((startTime - now.getTime()) / 1000) + auctionDate;
    var dateLeft = 0;
    var hourLeft = 0;
    var minuteLeft = 0;
    var secondLeft = 0;
    var hourZero = '';
    var minuteZero = '';
    var secondZero = '';
    if (ts < 0) {
        ts = 0;
    }else {
        dateLeft = parseInt(ts / 86400);
        ts = ts - dateLeft * 86400;
        hourLeft = parseInt(ts / 3600);
        ts = ts - hourLeft * 3600;
        minuteLeft = parseInt(ts / 60);
        secondLeft = ts - minuteLeft * 60;
    }
    if (hourLeft < 10) {
        hourZero = '0';
    }
    if (minuteLeft < 10) {
        minuteZero = '0';
    }
    if (secondLeft < 10) {
        secondZero = '0';
    }
    if (dateLeft > 0) {
        Temp = dateLeft + _day + hourZero + hourLeft + _hour + minuteZero + minuteLeft + _minute + secondZero + secondLeft + _second;
    }
    else {
        if (hourLeft > 0) {
            Temp = hourLeft + _hour + minuteZero + minuteLeft + _minute + secondZero + secondLeft + _second;
        }
        else {
            if (minuteLeft > 0) {
                Temp = minuteLeft + _minute + secondZero + secondLeft + _second;
            }
            else {
                if (secondLeft > 0) {
                    Temp = secondLeft + _second;
                }
                else {
                    Temp = '';
                }
            }
        }
    }
    if (auctionDate <= 0 || Temp == '') {
        Temp =  _end;
        leftTimeDOM.innerHTML = Temp;
        stopclock();
		if(leftTimeCallBack){
			leftTimeCallBack();
		}
        return;
    }
    leftTimeDOM.innerHTML =TempStartStr + Temp + TempEndStr;
    leftTimerID = setTimeout(showtime, 1000);
    timerRunning = true;
}

function stopclock() {
    if (timerRunning) {
        clearTimeout(leftTimerID);
    }
    timerRunning = false;
}

function macauclock() {
    stopclock();
    showtime();
}

/*显示时间的元素id，倒计时结束时的回调函数，开始时间*/
function onload_leftTime(obj) {
    obj = obj||{};
    _GMTEndTime = obj.endTime||0;
    TempStartStr = obj.startStr||TempStartStr;
    TempEndStr = obj.endStr||TempEndStr;
	_end = obj.timeEndStr||"";
    leftTimeDOM = document.getElementById(obj.id||"leftTime");
    if (!leftTimeDOM) {
        return;
        alert("显示时间的元素不存在!");
    }
    /*if (_GMTEndTime > 0) {
        if (obj.now_time == undefined) {
            var tmp_val = parseInt(_GMTEndTime) - parseInt(cur_date.getTime() / 1000 + cur_date.getTimezoneOffset() * 60);
        }
        else {
            var tmp_val = parseInt(_GMTEndTime) - obj.now_time;
        }
        if (tmp_val > 0) {
            auctionDate = tmp_val;
        }
    }*/
	/*修改2015-12-16传递_GMTEndTime为距离结束还剩多少秒*/
	auctionDate = _GMTEndTime;
	startTime = (new Date()).getTime();
	
    macauclock();
}
