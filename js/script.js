// Functions to get Max Number
function Max() {
    let valueOneString = document.getElementById('input_one').value;
    let valueTwoString = document.getElementById('input_two').value;
    let numberOne = parseFloat(valueOneString);
    let numberTwo = parseFloat(valueTwoString);
    // both value is present
    if (isNaN(numberOne) || isNaN(numberTwo)) {
        alert('Please enter two valid numbers.');
        return;
    }
    // both value is equal
    if (numberOne === numberTwo) {
        alert('Numbers are equal.');
        return;
    }
    let maxValue = Math.max(numberOne, numberTwo);
    document.getElementById('result').textContent = maxValue;
}
// Functions to get Reverse String
function Reverse() {
    let inputString = document.getElementById('input_string').value;
    if (inputString.length === 0) {
        alert('Please enter a string.');
        return;
    }
    let reversedString = inputString.split('').reverse().join('');
    document.getElementById('reversed_string').textContent = reversedString;
}
// Functions to get Longest Word
function FindLongestWord() {
    let inputSentence = document.getElementById('input_sentence').value;
    if (inputSentence.length === 0) {
        alert('Please enter a sentence.');
        return;
    }
    let words = inputSentence.split(' ');
    let longestWord = '';
    for (let i = 0; i < words.length; i++) {
        if (words[i].length > longestWord.length) {
            longestWord = words[i];
        }
    }
    document.getElementById('longest_word').textContent = longestWord;
}
// Functions to save name and number in cookies
function setCookie(cname, cvalue, exdays) {
    let d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    let expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + encodeURIComponent(cvalue) + ";" + expires + ";path=/";
}
function getCookie(cname) {
    let name = cname + "=";
    let ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i].trim();
        if (c.indexOf(name) === 0) {
            return decodeURIComponent(c.substring(name.length));
        }
    }
    return "";
}
function Save() {
    let name = document.getElementById('input_name').value.trim();
    let phone = document.getElementById('input_phone').value.trim();
    // Validate that name is not empty
    if (!name) {
        alert('Please enter a name.');
        return;
    }
    // Validate that phone is not empty
    if (!phone) {
        alert('Please enter a phone number.');
        return;
    }
    // Validate phone number format
    if (phone.length !== 10 || isNaN(phone)) {
        alert('Please enter a valid 10-digit phone number.');
        return;
    }
    setCookie("name", name, 365);
    setCookie("phone", phone, 365);
    // update UI immediately without reload
    document.getElementById('resume_name').textContent = name;
    document.getElementById('resume_phone').textContent = phone;
}
function Load() {
    let name = getCookie("name");
    let phone = getCookie("phone");
    if (name) {
        document.getElementById('resume_name').textContent = name
    }
    else {
        document.getElementById('resume_name').textContent = 'Smit Zaveri';
    }
    if (phone) {
        document.getElementById('resume_phone').textContent = phone
    }
    else {
        document.getElementById('resume_phone').textContent = '7043635077';
    }
    // Attach event listeners
    document.getElementById('btn_max').addEventListener('click', Max);
    document.getElementById('btn_reverse').addEventListener('click', Reverse);
    document.getElementById('btn_longest_word').addEventListener('click', FindLongestWord);
    document.getElementById('btn_save').addEventListener('click', Save);
}
window.onload = Load;