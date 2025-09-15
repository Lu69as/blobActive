function checkForm(f) {
    if (f.checkValidity()) {
        f.querySelector(`button`).classList.remove("invalid");
        document.querySelector(`.login_select .userId_list`).innerHTML.split("|").forEach((u) => {
            if (u == document.querySelector(`form.sign_up .userId`).value)
                document.querySelector(`form.sign_up button`).classList.add("invalid");
        });
    };
};

function updateNewHistory() {
    let arr = [];
    let json = JSON.parse(document.querySelector("input[name='currentPlanHistory']").value);
    if (json.length > 0) json.forEach(e => arr.push(e) );
    let lastDate = arr[0] == undefined ? null : arr[0].date;

    let dateArr = { date: new Date().toLocaleDateString('en-GB'), exercises: [] };
    document.querySelectorAll(".planTable tr:not(:has(th))").forEach((e) => {
        dateArr.exercises.push(`${e.children[0].firstChild.value}: 
            ${e.children[1].firstChild.value}x${e.children[2].firstChild.value}
            - ${e.children[3].firstChild.value}`.replace(/\s+/g, " ").trim())
    }); if (lastDate != dateArr.date) arr.push(dateArr);

    arr.sort((a, b) => b.date.split('/').reverse().join('') - a.date.split('/').reverse().join(''));
    document.querySelector("input[name='newPlanHistory']").value = JSON.stringify(arr);
}

window.addEventListener("load", () => {
    if (location.href.includes("/plans/"))updateNewHistory();
    document.querySelectorAll(".planTable td > input").forEach((e) => { e.addEventListener("change", () => {
        updateNewHistory();
        let id = e.parentElement.parentElement.id.replace("exercise_", "");
        let col = e.parentElement.classList[0].replace("exer_", "");

        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() { if (this.readyState == 4 && this.status == 200) console.log("Updated DB") };
        xmlhttp.open("GET", `../queries/exercise-update.php?id=${id}&col=${col}&val=${e.value}`, true);
        xmlhttp.send();
    })});

    document.querySelectorAll("form:is(.sign_up, .log_in)").forEach((e) => {
        checkForm(e);
        e.querySelectorAll("input").forEach((i) => i.addEventListener("input", () => checkForm(e)));
        e.addEventListener("submit", (s) => {
            if (e.querySelector(`button`).classList.contains("invalid")) s.preventDefault(); 
        });
    });

    document.querySelectorAll(".login_tabs > div").forEach((e) => { e.addEventListener("click", () => {
        let s = document.querySelector(".login_tabs div:not(."+ e.classList[0] +")");
        e.style.opacity = "1"; s.style.opacity = ".7";
        document.querySelector("form."+ s.classList[0]).style.display = "none";
        document.querySelector("form."+ e.classList[0]).style.display = "block";
    })});
})