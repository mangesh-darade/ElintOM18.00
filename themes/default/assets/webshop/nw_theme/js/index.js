document.addEventListener("DOMContentLoaded", function () {
  const searchParams = new URLSearchParams(window.location.search);
  const msg = searchParams.get("msg");
  const order_status = searchParams.get("order_status");
  console.log("order_statusorder_status", order_status);
  if (msg) {
    if (msg == "auth_success") {
      alert("Registered successfully");
    } else if (msg == "login_success") {
      alert("Logged in successfully");
    }
    window.location.href = baseUrl;
  } else if (order_status) {
    if (order_status == "success") {
      alert("Order placed successfully");
      window.location.href = baseUrl;
    }
  } else if (message) {
    console.log("messagemessage", message);
    // alert("You have been logged out successfully");
    alert(message);
  }

  const sealsArr = Array.from(document.querySelectorAll(".seal-img"));
  if (sealsArr.length)
    sealsArr.forEach((seal) => {
      seal.src = `${assets}nw_theme/images/${seal.alt}.png`;
      // console.log("sealseal", seal.src);
    });

  const linksArr = Array.from(document.querySelectorAll(".company-link"));
  if (linksArr.length)
    linksArr.forEach((link) => {
      link.href = `${baseUrl}`;
      // console.log("sealseal", seal.src);
    });
});
