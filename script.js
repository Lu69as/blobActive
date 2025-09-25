function checkForm(f) {
    if (f.checkValidity()) {
        f.querySelector(`button`).classList.remove("invalid");
        document.querySelector(`.login_select .userId_list`).innerHTML.split("|").forEach((u) => {
            if (u == document.querySelector(`form.sign_up .userId`).value)
                document.querySelector(`form.sign_up button`).classList.add("invalid");
        })
    }
}


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


function toggleSetsView(row) {
    let previousCheck = row.nextElementSibling;
    while (previousCheck != false) {
        previousCheck.classList.toggle("hide_set");
        nextCheck = previousCheck.nextElementSibling;

        if (!nextCheck) previousCheck = false;
        else if (nextCheck.classList.contains("set_row") 
            && !nextCheck.classList.contains("add_set")) previousCheck = nextCheck;
        else previousCheck = false;
    }
}



window.addEventListener("load", () => {
    // if (location.href.includes("/plans/")) updateNewHistory();
    document.querySelectorAll(".set_row td > input").forEach((e) =>
        e.addEventListener("change", () => {
            // updateNewHistory();
            let id = e.parentElement.parentElement.id.replace("set_", "");
            let col = e.parentElement.classList[0].replace("set_row_", "");

            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() { if (this.readyState == 4 && this.status == 200) console.log("Updated DB") };
            xmlhttp.open("GET", `../queries/exercise-update.php?id=${id}&col=${col}&val=${e.value}`, true);
            xmlhttp.send();
        })
    )


    document.querySelectorAll(".add_exercise_id .add_exercise_search").forEach((e) => {
        e.addEventListener("keydown", (evt) => { if (evt.key == "Enter") evt.preventDefault() })

        let ul = e.nextElementSibling;
        let firstLi = ul.firstElementChild;
        let li = Array.from(ul.children).slice(1);

        e.addEventListener("focus", () => { ul.classList.add("open") })
        e.addEventListener("blur", () => { setTimeout(() => ul.classList.remove("open"), 400) })
            
        ul.childNodes.forEach((c) => c.addEventListener("click", () => {
            ul.classList.remove("open");
            let query = e.value.toLowerCase();
            let hiddenField = e.previousElementSibling;
            let cId = c.id.replace("exercise_", "");

            hasDoneCode = false;
            if (cId != "addNew") {
                e.value = c.innerHTML.toLowerCase();
                hiddenField.value = cId;
                hasDoneCode = true;
            }
            else {
                li.forEach((l) => { if (l.innerHTML == query.toLowerCase()) {
                    hiddenField.value = l.id.replace("exercise_", "");
                    hasDoneCode = true;
                }})
            }
            if (!hasDoneCode) hiddenField.value = "addNew";
        }))

        e.addEventListener("input", () => {
            let query = e.value.toLowerCase();

            li.sort((a, b) => {
                const textA = a.textContent.toLowerCase();
                const textB = b.textContent.toLowerCase();

                const aMatch = textA.includes(query);
                const bMatch = textB.includes(query);

                if (aMatch && !bMatch) return -1;
                if (!aMatch && bMatch) return 1;

                return textA.localeCompare(textB);
            });

            ul.innerHTML = "";
            ul.append(firstLi);
            li.forEach((item) => {
                ul.append(item);
                if (query.toLowerCase() == item.innerHTML.toLowerCase())
                    e.previousElementSibling.value = item.id
            });
        })
    })
    document.querySelectorAll("tr[id^='exercise_'] + .set_row").forEach((e) => toggleSetsView(e) )


    document.querySelectorAll(`input[inputmode='numeric']`).forEach((e) => 
        e.addEventListener('input', () => { e.value = e.value.replace(/[^0-9]/g, '') }))
    document.querySelectorAll(`input:not([inputmode='numeric'])`).forEach((e) => 
        e.addEventListener('input', () => { e.value = e.value.replace(/[^A-Za-z0-9?!$%&', ]/g, '') }))


    document.querySelectorAll("form:is(.sign_up, .log_in)").forEach((e) => { checkForm(e);
        e.querySelectorAll("input").forEach((i) => i.addEventListener("input", () => checkForm(e)));
        e.addEventListener("submit", (s) => {
            if (e.querySelector(`button`).classList.contains("invalid")) s.preventDefault(); 
        })
    })


    document.querySelectorAll(".login_tabs > div").forEach((e) =>
        e.addEventListener("click", () => {
            let s = document.querySelector(".login_tabs div:not(."+ e.classList[0] +")");
            e.style.opacity = "1"; s.style.opacity = ".7";
            document.querySelector("form."+ s.classList[0]).style.display = "none";
            document.querySelector("form."+ e.classList[0]).style.display = "block";
        })
    )
})