// Check if login / signup form is valid
function checkForm(f) {
    if (f.checkValidity()) {
        f.querySelector(`button`).classList.remove("invalid");
        document.querySelector(`.login_select .userId_list`).innerHTML.split("|").forEach((u) => {
            if (u == document.querySelector(`form.sign_up .userId`).value)
                document.querySelector(`form.sign_up button`).classList.add("invalid");
        })
    }
}


// Toggle set rows to be viewed or not
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


// Update history section of a plan site
function getPlanHistory(planId, sort) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.querySelector(".plan.workoutHistory > div").innerHTML = xmlhttp.responseText;
            document.querySelectorAll(".plan.workoutHistory > div h2").forEach((e) =>
                e.addEventListener("click", () => {
                    if (e.children.length == 0 || !e.querySelector("b").hasAttribute("contenteditable"))
                        e.parentElement.classList.toggle("showChildren")
                })
            )
        }
    }
    xmlhttp.open("GET", `../queries/exercise-update.php?history_id=${planId}&sort=${sort}`, true);
    xmlhttp.send();
}


function toggleEditHistory() {
    document.querySelectorAll("b.historyEditField").forEach((e) => {
        if (!e.classList.contains("editable")) {
            e.addEventListener("input", () => {
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function() { console.log("Updated") };
                xmlhttp.open("GET", `../queries/exercise-update.php?${e.getAttribute("data-label")}&val=${e.innerText}`, true);
                xmlhttp.send();
            })
            e.classList.add("editable");
        }
        e.toggleAttribute("contenteditable");
    })
}


// All code running after site has loaded
window.addEventListener("load", () => {
    document.querySelectorAll(".set_row td > input").forEach((e) =>
        e.addEventListener("change", () => {
            let id = e.parentElement.parentElement.id.replace("set_", "");
            let col = e.parentElement.classList[0].replace("set_row_", "");

            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() { if (this.readyState == 4 && this.status == 200) console.log("Updated DB") };
            xmlhttp.open("GET", `../queries/exercise-update.php?table=plan_sets&tableId=plan_setId&update_id=${id}&col=${col}&val=${e.value}`, true);
            xmlhttp.send();
        })
    )
    
    document.querySelectorAll(".editBtns .workout").forEach((e) =>
        e.addEventListener("click", () => {
            let id = new URLSearchParams(window.location.search).get("p");
            if (id == null) return;

            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() { if (this.readyState == 4 && this.status == 200) console.log("Saved workout") };
            xmlhttp.open("GET", `../queries/exercise-update.php?save_id=${id}`, true);
            xmlhttp.send();
        })
    )


    // Code for adding an exercise to the plan
    document.querySelectorAll(".add_exercise_id .add_exercise_search").forEach((e) => {
        e.addEventListener("keydown", (evt) => { if (evt.key == "Enter") evt.preventDefault() })

        let ul = e.nextElementSibling;
        let firstLi = ul.firstElementChild;
        let li = Array.from(ul.children).slice(1);

        e.addEventListener("focus", () => { ul.classList.add("open") })
        e.addEventListener("blur", () => { setTimeout(() => ul.classList.remove("open"), 400) })
        
        // Check if it should make a new exercise or just add an existing one
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

        // Sort the exercises in box by what is in search field
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
    // document.querySelectorAll("tr[id^='exercise_'] + .set_row").forEach((e) => toggleSetsView(e) )


    // Confirm if user want to delete domething
    document.querySelectorAll(".set_row .remove button").forEach((e) => 
        e.addEventListener("click", (evt) => { if (!confirm("Are you sure you want to " + e.getAttribute("title").toLowerCase())) evt.preventDefault() }))


    // Only allow typing of certain characters in input fields across the site
    document.querySelectorAll(`input[inputmode='numeric']`).forEach((e) => 
        e.addEventListener('input', () => { e.value = e.value.replace(/[^0-9]/g, '') }))
    document.querySelectorAll(`input:not([inputmode='numeric'])`).forEach((e) => 
        e.addEventListener('input', () => { e.value = e.value.replace(/[^A-Za-z0-9?!$%&',. ]/g, '') }))


    // Validate log in / sign up form
    document.querySelectorAll("form:is(.sign_up, .log_in)").forEach((e) => { checkForm(e);
        e.querySelectorAll("input").forEach((i) => i.addEventListener("input", () => checkForm(e)));
        e.addEventListener("submit", (s) => {
            if (e.querySelector(`button`).classList.contains("invalid")) s.preventDefault(); 
        })
    })

    // Click on log in / sign up to change
    document.querySelectorAll(".login_tabs > div").forEach((e) =>
        e.addEventListener("click", () => {
            let s = document.querySelector(".login_tabs div:not(."+ e.classList[0] +")");
            e.style.opacity = "1"; s.style.opacity = ".7";
            document.querySelector("form."+ s.classList[0]).style.display = "none";
            document.querySelector("form."+ e.classList[0]).style.display = "block";
        })
    )
})