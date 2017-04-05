function _hasPopupBlocker(poppedWindow) {
	// http://stackoverflow.com/questions/668286/detect-blocked-popup-in-chrome
	var result = false;

	try {
		if (typeof poppedWindow == 'undefined') {
			// Safari with popup blocker... leaves the popup window handle undefined
			result = true;
		}
		else if (poppedWindow && poppedWindow.closed) {
			// This happens if the user opens and closes the client window...
			// Confusing because the handle is still available, but it's in a "closed" state.
			// We're not saying that the window is not being blocked, we're just saying
			// that the window has been closed before the test could be run.
			result = false;
		}
		else if (poppedWindow && poppedWindow.test) {
			// This is the actual test. The client window should be fine.
			result = false;
		}
		else {
			// Else we'll assume the window is not OK
			result = true;
		}

	} catch (err) {
		//if (console) {
		//    console.warn("Could not access popup window", err);
		//}
	}

	return result;
}

function others(str)
{
	var win;
	//location.href="http://www.google.co.jp/search?q="+encodeURIComponent(str);
	win = delayOpen(win, "https://github.com/dotcominternet/BookmakerFrontend/search?type=Issues&utf8=%E2%9C%93&q="+encodeURIComponent(str));
	win = delayOpen(win, "http://itwiki.office/index.php?title=Special%3ASearch&search="+encodeURIComponent(str));
	win = delayOpen(win, "http://helpdesk.office/scp/tickets.php?a=search&query="+encodeURIComponent(str));
}

function delayOpen(win, nex_url, name) {
	if(!name) {
		name = "_blank";
	}

	if(!win) {
		return window.open(nex_url, name);
	}

	var rtn;
	$(win).ready(function() {
		//rtn = window.open(nex_url, name);
		setTimeout(function() {
			rtn = window.open(nex_url, name);
		}, 1000);
	});
	return rtn;
}
