document.addEventListener("DOMContentLoaded", () => {
  const registerForm = document.getElementById("registerForm");
  const registerMessage = document.getElementById("registerMessage");

  if (!registerForm) return;

  registerForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    registerMessage.innerHTML = "";

    const formData = new FormData(registerForm);

    try {
      const response = await fetch("index.php", {
        method: "POST",
        body: formData,
      });

      const data = await response.json();

      if (data.status === "success") {
        registerMessage.innerHTML = `
                    <div style="
                        background:#d4edda;
                        color:#155724;
                        padding:10px;
                        border-radius:5px;
                        margin-bottom:15px;
                    ">
                        ${data.message}
                    </div>
                `;

        setTimeout(() => {
          window.location.reload();
        }, 1200);
      } else {
        registerMessage.innerHTML = `
                    <div style="
                        background:#f8d7da;
                        color:#721c24;
                        padding:10px;
                        border-radius:5px;
                        margin-bottom:15px;
                    ">
                        ${data.message}
                    </div>
                `;
      }
    } catch (err) {
      registerMessage.innerHTML = `
                <div style="
                    background:#f8d7da;
                    color:#721c24;
                    padding:10px;
                    border-radius:5px;
                    margin-bottom:15px;
                ">
                    Błąd połączenia z serwerem.
                </div>
            `;
    }
  });
});
