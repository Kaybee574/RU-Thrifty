//using navigator objects to get the language, userAgent, geolocation, permissions and online status of the browser

//checks if the browser is connected to the internet
console.log(navigator.onLine);

//checks if access to certain things is provided
console.log(navigator.permissions);

//checks the location of the user
console.log(
  navigator.geolocation.getCurrentPosition((position) => {
    // This code only runs AFTER the user clicks "Allow"
    console.log("Latitude:", position.coords.latitude);
    console.log("Longitude:", position.coords.longitude);
  }),
);

//returns the browser name, version and operating system
console.log(navigator.userAgent);

//return the language used by the user
console.log(navigator.language);

//checks if cookies are enabled
console.log(navigator.cookieEnabled);

console.log(navigator.clipboard);

//access the forms through the form id
const form1 = document.querySelector(".flex-form.signup-form");
const form2 = document.querySelector(".flex-form.signin-form");
const form3 = document.querySelector(".forgot_password");
const email = document.getElementById("email");
const password = document.getElementById("password");
const fname = document.getElementById("name");
const password2 = document.getElementById("confirm_password");

//validates input for the sign up page
if (form1) {
  form1.addEventListener("submit", (e) => {
    e.preventDefault();
    validateInputs();
  });
}

//validates input for the sign in page
if (form2) {
  form2.addEventListener("submit", (e) => {
    e.preventDefault();
    checkInputs();
  });
}

//validates input for the forgot password page
if (form3) {
  form3.addEventListener("submit", (e) => {
    e.preventDefault();
    checkEmail();
  });
}
//the function that validates the input for the sign up page
function validateInputs() {
  //get the values and remove any whitespaces
  const fnameValue = fname.value.trim();
  const emailValue = email.value.trim();
  const passwordValue = password.value.trim();
  const password2Value = password2.value.trim();

  //if name is not entered
  if (fnameValue === "") {
    //calls a function that returns an error message
    setErrorFor(fname, "Name cannot be blank");
  } else {
    //calls a function that returns a success message
    setSuccessFor(fname);
  }

  if (emailValue === "") {
    //calls a function that returns an error message
    setErrorFor(email, "Email cannot be blank");
  } else if (!isEmail(emailValue)) {
    setErrorFor(email, "Email is invalid");
  } else {
    //calls the function if the email is valid
    setSuccessFor(email);
  }

  if (passwordValue === "") {
    //calls a function that returns an error message
    setErrorFor(password, "Password cannot be blank");
  } else {
    //calls the function if the password is valid
    setSuccessFor(password);
  }

  if (password2Value === "") {
    //calls a function that returns an error message
    setErrorFor(password2, "Confirm Password cannot be blank");
  } else if (passwordValue !== password2Value) {
    //checks if passwords match
    setErrorFor(password2, "Passwords do not match");
  } else {
    //calls the function if the password is valid
    setSuccessFor(password2);
  }
}

function checkInputs() {
  const emailValue = email.value.trim();
  const passwordValue = password.value.trim();
  let isValid = true;

  if (emailValue === "") {
    //calls a function that returns an error message
    setErrorFor(email, "Email cannot be blank");
    isValid = false;
  } else if (!isEmail(emailValue)) {
    //checks the validity of the email through a function that checks for the syntax
    setErrorFor(email, "Email is invalid");
    isValid = false;
  } else {
    //calls the function if the email is valid
    setSuccessFor(email);
  }

  if (passwordValue === "") {
    //calls a function that returns an error message
    setErrorFor(password, "Password cannot be blank");
    isValid = false;
  } else {
    setSuccessFor(password);
  }

  if (isValid) {
    window.location.href = "Explore.html"; // Page Redirect
  }
}

function checkEmail() {
  const emailValue = email.value.trim();

  if (emailValue === "") {
    //calls a function that returns an error message
    setErrorFor(email, "Email cannot be blank");
  } else if (!isEmail(emailValue)) {
    //checks if the email is of the correct syntax through a function
    setErrorFor(email, "Email is invalid");
  } else {
    setSuccessFor(email);
    alert("Password reset email sent successfully!");
  }
}

//returns error message
function setErrorFor(input, message) {
  const formrow = input.parentElement;
  const small = formrow.querySelector("small");
  small.innerText = message;
  formrow.className = "formrow error";
}

//returns success message
function setSuccessFor(input) {
  const formrow = input.parentElement;
  formrow.className = "formrow success";
}

//checks if the email is of the correct syntax
function isEmail(email) {
  const regex =
    /^[gG][0-9][0-9][aA-zZ][0-9][0-9][0-9][0-9]@campus\.ru\.ac\.za$/;
  return regex.test(email);
}

/* Function that displays Navigator Info */
function displayNavigatorInfo() {
  const infoDiv = document.getElementById("browserInfo");
  if (!infoDiv) return;
  const properties = [
    `Browser Language: ${navigator.language}`,
    `Online: ${navigator.onLine ? "Yes" : "No"}`,
    `Cookies Enabled: ${navigator.cookieEnabled ? "Yes" : "No"}`,
    `User Agent: ${navigator.userAgent}`,
    `Platform: ${navigator.platform}`,
    `Java Enabled: ${navigator.javaEnabled() ? "Yes" : "No"}`,
  ];
  infoDiv.innerHTML =
    "<strong>Browser Information:</strong><br>" + properties.join("<br>");
}

// Show/hide browser info on button click
const btnInfo = document.getElementById("showBrowserInfo");
if (btnInfo) {
  btnInfo.addEventListener("click", () => {
    const infoDiv = document.getElementById("browserInfo");
    if (infoDiv) {
      if (infoDiv.style.display === "none") {
        displayNavigatorInfo();
        infoDiv.style.display = "block";
      } else {
        infoDiv.style.display = "none";
      }
    }
  });
}

/*  Navigator method to log when user leaves page   */
window.addEventListener("beforeunload", () => {
  navigator.sendBeacon("/log.php", "User left page");
});

// Change header background on mouseover/mouseout
const header = document.getElementById("header1");
if (header) {
  header.addEventListener("mouseover", () => {
    header.style.backgroundColor = "#ffaa50";
  });
  header.addEventListener("mouseout", () => {
    header.style.backgroundColor = "";
  });
}

// Dynamic footer message
const footer = document.querySelector(".site-footer");
if (footer) {
  const newPara = document.createElement("p");
  newPara.textContent = "Thank you for visiting RU Thrifty!";
  newPara.style.fontSize = "0.8rem";
  newPara.style.color = "#ddd";
  newPara.classList.add("footer-message");
  footer.appendChild(newPara);
}

// Toggle a class on navigation when clicked
const nav = document.querySelector(".main-nav");
if (nav) {
  nav.addEventListener("click", () => {
    nav.classList.toggle("nav-highlight");
  });
}

// Email validation on input
const emailInput = document.getElementById("email");
if (emailInput) {
  emailInput.addEventListener("input", function () {
    if (isEmail(this.value)) {
      this.style.borderColor = "green";
    } else {
      this.style.borderColor = "red";
    }
  });
}

/* Functions for slideshowing  */
let slideIndex = 1;

function plusSlides(n) {
  showSlides((slideIndex += n));
}

function currentSlide(n) {
  showSlides((slideIndex = n));
}

function showSlides(n) {
  let i;
  let slides = document.getElementsByClassName("slide");
  let dots = document.getElementsByClassName("dot");
  if (slides.length === 0) return;
  if (n > slides.length) {
    slideIndex = 1;
  }
  if (n < 1) {
    slideIndex = slides.length;
  }
  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";
  }
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active", "");
  }
  slides[slideIndex - 1].style.display = "block";
  dots[slideIndex - 1].className += " active";
}

// Initialize slideshow if elements exist
document.addEventListener("DOMContentLoaded", function () {
  if (document.getElementsByClassName("slide").length > 0) {
    showSlides(slideIndex);
  }
});

/*  Multi-column layout toggle  */
const toggleBtn = document.getElementById("toggleColumns");
const grid = document.querySelector(".how-it-works-grid");
if (toggleBtn && grid) {
  toggleBtn.addEventListener("click", () => {
    if (grid.style.gridTemplateColumns === "1fr") {
      grid.style.gridTemplateColumns = "1fr 1fr";
      toggleBtn.textContent = "1 Column View";
    } else {
      grid.style.gridTemplateColumns = "1fr";
      toggleBtn.textContent = "2 Columns View";
    }
  });
}

/* Function for form validation */
// Initialiser for forms
function initFormValidation(formId, fields) {
  const form = document.getElementById(formId);
  if (!form) return;

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    let isValid = true;
    fields.forEach((field) => {
      const input = document.getElementById(field.id);
      if (!input) return;
      const value = input.value.trim();
      let errorMsg = "";
      if (field.required && value === "") {
        errorMsg = `${field.label} cannot be blank`;
      } else if (field.type === "email" && !isEmail(value)) {
        errorMsg =
          "Email must be a valid Rhodes student email (e.g., g23a1234@campus.ru.ac.za)";
      } else if (field.type === "password" && field.confirmId) {
        const confirm = document.getElementById(field.confirmId).value.trim();
        if (value !== confirm) errorMsg = "Passwords do not match";
      } else if (field.pattern && !field.pattern.test(value)) {
        errorMsg = field.patternMsg;
      }
      if (errorMsg) {
        setErrorFor(input, errorMsg);
        isValid = false;
      } else {
        setSuccessFor(input);
      }
    });
    if (isValid) {
      if (formId === "signinForm") window.location.href = "Explore.html";
      else if (formId === "signupForm") alert("Account created! (demo)");
      else if (formId === "forgotForm") alert("Password reset email sent!");
    }
  });
}

// Initialise forms
document.addEventListener("DOMContentLoaded", () => {
  initFormValidation("signinForm", [
    { id: "login_id", label: "Email", required: true, type: "email" },
    { id: "password", label: "Password", required: true },
  ]);
  initFormValidation("signupForm", [
    { id: "name", label: "Full name", required: true },
    { id: "email", label: "Email", required: true, type: "email" },
    {
      id: "password",
      label: "Password",
      required: true,
      pattern: /^(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,16}$/,
      patternMsg:
        "Password must be 8-16 characters, include 1 uppercase and 1 number",
    },
    {
      id: "confirm_password",
      label: "Confirm password",
      required: true,
      type: "password",
      confirmId: "password",
    },
  ]);
  initFormValidation("forgotForm", [
    { id: "email", label: "Email", required: true, type: "email" },
  ]);
});

/* Creative Addition: Dynamic greeting based on time of the day, referd to https://codingartistweb.com/2025/05/custom-greetings-with-html-css-and-javascript/*/
function setGreeting() {
  const hour = new Date().getHours();
  let greeting;
  if (hour < 12) greeting = "Good morning";
  else if (hour < 18) greeting = "Good afternoon";
  else greeting = "Good evening";
  const greetingEl = document.getElementById("greeting");
  if (greetingEl)
    greetingEl.textContent = `${greeting}, Rhodent! Welcome to RU Thrifty`;
}
setGreeting();
