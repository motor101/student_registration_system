let text_inputs = document.querySelectorAll("div.selection input[type='text']");

text_inputs.forEach(function (element) {
    add_text_validation_listeners(element);
});

let error_field = document.getElementById("error_field");
let error_table = document.querySelector("#error_field table");

let birth_date = document.getElementById("birth_date")
let birth_date_warning = document.getElementById("birth_date_warning")

birth_date.addEventListener("input", validate_date);

let zodiac_name = document.getElementById("zodiac_name");
let zodiac_sign = document.getElementById("zodiac_sign");

let canvas = document.getElementById("canvas");

function activate_signature_support() {

    let context = canvas.getContext("2d");
    context.fillStyle = "black";

    let mouseDown = 0;

    document.body.onmousedown = function () {
        mouseDown = 1;
    }

    document.body.onmouseup = function () {
        mouseDown = 0;
    }

    canvas.addEventListener("mousemove", draw_point_if_mouse_down);
    canvas.addEventListener("mousedown", draw_point);

    function draw_point(event) {
        let rect = canvas.getBoundingClientRect();
        let x = event.clientX - rect.x;
        let y = event.clientY - rect.y;
        context.fillRect(x, y, 4, 3);
    }

    function draw_point_if_mouse_down(event) {
        if (mouseDown) {
            draw_point(event);
        }
    }
}

function activate_download_canvas() {
    document.getElementById('download_canvas').onclick = function () {
        canvas.toBlob(function (blob) {
            let url = window.URL.createObjectURL(blob);

            let a = document.createElement("a");

            a.href = url;
            a.download = "signature.png";

            a.click();

            window.URL.revokeObjectURL(url);
        }, "image/png");
    }
}

function activate_clear_canvas() {
    let context = canvas.getContext("2d");

    let clear_canvas_btn = document.getElementById("clear_canvas");

    clear_canvas_btn.addEventListener("click", function () {
        context.clearRect(0, 0, canvas.width, canvas.height);
    });
}

activate_signature_support();
activate_download_canvas();
activate_clear_canvas();

const form = document.getElementById("form");

form.addEventListener("submit", function (event) {
    event.preventDefault();
    sendData();
});

function sendData() {
    const xhr = new XMLHttpRequest();

    const form_data = new FormData(form);

    xhr.addEventListener("load", function (event) {
            let response = JSON.parse(event.target.responseText);

            if (response.success) {
                window.location.href = response.new_url;
            } else {
                // thow the error table
                error_field.style.display = "block";

                // remove old errors from the table, if any
                let tbody = document.querySelector("#error_field table tbody")
                error_table.removeChild(tbody);
                tbody = document.createElement("tbody");
                error_table.appendChild(tbody);

                for (let key in response.error_message) {
                    if (response.error_message.hasOwnProperty(key)) {
                        let new_row = document.createElement("tr");

                        let field_name = document.createElement("td");
                        field_name.innerText = key;

                        let error_description = document.createElement("td");
                        error_description.innerText = response.error_message[key];

                        new_row.appendChild(field_name);
                        new_row.appendChild(error_description);
                        tbody.appendChild(new_row);
                    }
                }
            }
        }
    )


    xhr.addEventListener("error", function () {
        alert('Не можем да обработим заявката Ви. Опитайте пак след малко.');
    });

    xhr.open("POST", "registration_handler.php");

// add zodiac sign for sending to the server
    form_data.append("zodiac_sign", zodiac_sign.innerText);

    form_data.append("zodiac_name", zodiac_name.innerText);

    canvas.toBlob(function (blob) {
        form_data.append("signature", blob, "signature");

        // We have to write xhr here, because otherwise if we write it after the callback function, there's
        // a chance that the xhr will be sent before the callback executes and the BLOB won't be included in the
        // request.
        xhr.send(form_data);
    }, "image/png");
}


const zodiac_names = ["козирог", "водолей", "риби", "овен", "телец", "близнаци", "рак", "лъв", "дева", "везни",
    "скорпион", "стрелец"];

const zodiac_signs = ["\u2651", "\u2652", "\u2653", "\u2648", "\u2649", "\u264a", "\u2648", "\u264c", "\u264d",
    "\u264e", "\u264f", "\u2650"
];

const last_day = [19, 19, 20, 20, 21, 21, 22, 23, 23, 22, 22, 21];

function validate_date(event) {
    let min_date = new Date("1900-01-01");
    let max_date = new Date("2005-01-01");
    let date = new Date(event.target.value);
    if (date < min_date) {
        birth_date_warning.innerText = "Посочили сте твърде голяма възраст";
        birth_date_warning.style.color = "red";
    } else if (date > max_date) {
        birth_date_warning.innerText = "Твърде сте млад, за да сте студент";
        birth_date_warning.style.color = "red";
    } else {
        birth_date_warning.innerText = "Датата е валидна";
        birth_date_warning.style.color = "green";
        set_zodiac_info(date.getDate(), date.getMonth());
    }
}

function set_zodiac_info(day, month) {
    let index = month;
    if (day > last_day[month]) {
        index = (month + 1) % 12;
    }
    zodiac_name.innerText = zodiac_names[index];
    zodiac_sign.innerText = zodiac_signs[index];
}

function add_text_validation_listeners(element) {
    element.addEventListener("input", function () {
        check_text_value(element)
    });
}

function check_text_value(element) {
    let warning_element = document.querySelector("#" + element.id + "+span");
    let str = element.value;
    if (str !== null) {
        for (let i = 0; i < str.length; ++i) {
            let symbol = str[i];
            if (symbol >= "0" && symbol <= "9") {
                warning_element.innerHTML = "Това поле не трябва да съдържа цифри";
                warning_element.style.color = "red"
                return;
            }
        }
        if (str.length === 0) {
            warning_element.innerHTML = "Това поле не трябва да е празно";
            warning_element.style.color = "red"
            return;
        }
        warning_element.innerHTML = "Полето е валидно";
        warning_element.style.color = "green"
    }
}


