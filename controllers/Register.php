<?php
$title="Signin | Lpgas ";
include_once '../includes/header.php';?>

<script>
  function selectCustomerType(value) {
    const label = document.getElementById("Customer_type");
    if (value === "individual") {
      label.textContent = "NIC Number";
    } else if (value === "business") {
      label.textContent = "Business Registration Number";
    } else {
      label.textContent = "NIC Number ";
    }
  }
</script>

<style>
	    .error {
      color: red;
      font-size: 14px;
    }
    .success {
      color: green;
      font-size: 14px;
    }
</style>


<section class="vh-100" style="background-color: #eee;">
	<div class="container h-100">
		<div class="row d-flex justify-content-center align-items-center h-100">
			<div class="col-lg-12 col-xl-11">
				<div class="card text-black" style="border-radius: 25px;">
					<div class="card-body p-md-5">
						<div class="row justify-content-center">
							<div class="col-md-10 col-lg-6 col-xl-5 order-2 order-lg-1">

								<p class="text-center h1 fw-bold mb-5 mx-1 mx-md-4 mt-4">Sign up</p>

								<form class="mx-1 mx-md-4" method="post" id="Signup"  action="../controllers/signup.php" >

									<div class="d-flex flex-row align-items-center mb-4">
										<i class="fas fa-user fa-lg me-3 fa-fw"></i>
										<div data-mdb-input-init class="form-outline flex-fill mb-0">
											<input type="text" id="name"  class="form-control" required   name="name" />
											<label class="form-label" for="name"> Name</label>
										</div>
									</div>

									<div class="d-flex flex-row align-items-center mb-4">
										<i class="fas fa-envelope fa-lg me-3 fa-fw"></i>
										<div data-mdb-input-init class="form-outline flex-fill mb-0">
											<input type="email" id="email_id" class="form-control" name="email"  required />
											<div id="emailMessage" class="mt-2"></div>

											<label class="form-label" for="email_id"> Email</label>
											
										</div>
									</div>

									<div class="d-flex flex-row align-items-center mb-4">
										<i class="fas fa-address-book fa-lg me-3 fa-fw"></i>
										<div data-mdb-input-init class="form-outline flex-fill mb-0">
											<input type="text" id="form3Example3c"  class="form-control" name="Address" required />
											<label class="form-label" for="form3Example3c">Address</label>
										</div>
									</div>

									<div class="d-flex flex-row align-items-center mb-4">
										<i class="fas fa-phone fa-lg me-3 fa-fw"></i>
										<div data-mdb-input-init class="form-outline flex-fill mb-0">
											<input type="tel" id="contact_number_id" name="contact_number" class="form-control" required />
											<div id="contact_numberMessage" class="mt-2"></div>
											<label class="form-label" for="contact_number">Contact Number</label>
										</div>
									</div>

									<div class="d-flex flex-row align-items-center mb-4">
										<i class="fas fa-user-group fa-lg me-3 fa-fw"></i>
										<div data-mdb-input-init class="form-outline flex-fill mb-0">
											<select class="form-select" id="selectCustomerType"  aria-label="Default select example" required  onchange="selectCustomerType(this.value)"  name="customer_type">
												<option selected value="individual">Individual</option>
												<option value="business">Business</option>
											</select>
											<label class="form-label" for="selectCustomerType"> Customer type</label>
										</div>
									</div>



									<div class="d-flex flex-row align-items-center mb-4">
										<i class="fas fa-hashtag fa-lg me-3 fa-fw"></i>
										<div data-mdb-input-init class="form-outline flex-fill mb-0"> 
											<input type="text" id="nic_id" name="nic_or_registration_number" class="form-control" required />
											<div id="nic_or_registration_numberMessage" class="mt-2"></div>
											<label  id="Customer_type" class="form-label"   for="nic_id">NIC Number</label>
											
										</div>
									</div>




			<div class="d-flex flex-row align-items-center mb-4">
        <i class="fas fa-key fa-lg me-3 fa-fw"></i>
        <div class="form-outline flex-fill mb-0">
          <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required />
          <label class="form-label" for="password">Password</label>
          <ul class="mt-2">
            <li id="ruleLength" class="error">At least 8 characters</li>
            <li id="ruleLetter" class="error">At least one letter</li>
            <li id="ruleNumber" class="error">At least one number</li>
          </ul>
        </div>
      </div>



			<div class="d-flex flex-row align-items-center mb-4">
        <i class="fas fa-key fa-lg me-3 fa-fw"></i>
        <div class="form-outline flex-fill mb-0">
          <input type="password" id="repeatPassword" class="form-control" placeholder="Repeat your password" required />
          <label class="form-label" for="repeatPassword">Repeat your password</label>
          <div id="matchMessage" class="mt-2"></div>
        </div>
      </div>


									<div class="form-check d-flex justify-content-center mb-5">
										<input class="form-check-input me-2" type="checkbox" value="" required id="form2Example3c" />
										<label class="form-check-label" for="form2Example3c">
											I agree all statements in <a href="#!">Terms of service</a>
										</label>
									</div>

									<div class="d-flex justify-content-center mx-4 mb-3 mb-lg-4">
										<button type="submit"  id="submitBtn" data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-lg" disabled>Register</button>
									</div>

								</form>

							</div>
							<div class="col-md-10 col-lg-6 col-xl-7 d-flex align-items-center order-1 order-lg-2">

								<img src="../assets/images/draw1.webp"
									class="img-fluid" alt="Sample image">

							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>




<script>
    document.getElementById('Signup').addEventListener('input', validatePassword);
    const submitBtn = document.getElementById('submitBtn');


    function validatePassword() {
      const password = document.getElementById('password').value;
      const repeatPassword = document.getElementById('repeatPassword').value;
  
      const matchMessage = document.getElementById('matchMessage');
      const ruleLength = document.getElementById('ruleLength');
      const ruleLetter = document.getElementById('ruleLetter');
      const ruleNumber = document.getElementById('ruleNumber');

      // Validation rules
      const hasLength = password.length >= 8;
      const hasLetter = /[A-Za-z]/.test(password);
      const hasNumber = /\d/.test(password);

      // Update rule messages
      ruleLength.className = hasLength ? "success" : "error";
      ruleLetter.className = hasLetter ? "success" : "error";
      ruleNumber.className = hasNumber ? "success" : "error";

      // Check password validity
      const isPasswordValid = hasLength && hasLetter && hasNumber;

      // Check if passwords match
      if (isPasswordValid) {
        if (repeatPassword === "") {
          matchMessage.textContent = "Please re-enter your password.";
          matchMessage.className = "error";
          submitBtn.disabled = true;
        } else if (password === repeatPassword) {
          matchMessage.textContent = "Passwords match!";
          matchMessage.className = "success";
          submitBtn.disabled = false;
        } else {
          matchMessage.textContent = "Passwords do not match!";
          matchMessage.className = "error";
          submitBtn.disabled = true;
        }
      } else {
        matchMessage.textContent = "";
        submitBtn.disabled = true;
      }
    }




document.getElementById('Signup').addEventListener('input', function (e) {
    const fieldMap = {
        email: document.querySelector('input[name="email"]'),
        nic_or_registration_number: document.querySelector('input[name="nic_or_registration_number"]'),
				contact_number: document.querySelector('input[name="contact_number"]'),
				
    };

    for (const [field, element] of Object.entries(fieldMap)) {
        if (element.value.trim() !== '') {
            checkExists(field, element.value.trim());
        }
    }
});

function checkExists(field, value) {
    fetch('../controllers/check_exists.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({ field, value }),
    })
        .then((response) => response.json())
        .then((data) => {
            const messageElement = document.getElementById(`${field}Message`);
            if (data.exists) {
                messageElement.textContent = data.message;
                messageElement.className = 'error';
								submitBtn.disabled = true;
            } else {
                messageElement.textContent = data.message;
                messageElement.className = 'success';
            }
        })
        .catch((error) => console.error('Error:', error));
}




  </script>