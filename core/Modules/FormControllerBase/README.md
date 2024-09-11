
```php
$form['first_name'] = array(
            '#type' => 'text',
            '#title' => 'First Name',
            '#required' => true,
            '#placeholder' => 'First Name',
            '#attributes' => array('class' => 'form-control', 'id' => 'first_name'),
            '#description' => 'Please enter your first name.',
            '#default_value' => !empty($formState->get('first_name')) ? $formState->get('first_name') : '',
        );

        $form['email'] = array(
            '#type' => 'email',
            '#title' => 'Email',
            '#required' => true,
            '#placeholder' => 'Email Address',
            '#attributes' => array('class' => 'form-control', 'id' => 'email'),
            '#description' => 'Please enter your email address.',
            '#default_value' => !empty($formState->get('email')) ? $formState->get('email') : '',
        );

        $form['password'] = array(
            '#type' => 'password',
            '#title' => 'Password',
            '#required' => true,
            '#placeholder' => 'Password',
            '#attributes' => array('class' => 'form-control', 'id' => 'password'),
            '#description' => 'Please enter a strong password.',
        );

        $form['confirm_password'] = array(
            '#type' => 'password',
            '#title' => 'Confirm Password',
            '#required' => true,
            '#placeholder' => 'Confirm Password',
            '#attributes' => array('class' => 'form-control', 'id' => 'confirm_password'),
            '#description' => 'Please confirm your password.',
        );

        $form['bio'] = array(
            '#type' => 'textarea',
            '#title' => 'Bio',
            '#placeholder' => 'Tell us a bit about yourself...',
            '#attributes' => array('class' => 'form-control', 'id' => 'bio'),
            '#description' => 'Provide a short biography.',
        );

        $form['gender'] = array(
            '#type' => 'radios',
            '#title' => 'Gender',
            '#options' => array('male' => 'Male', 'female' => 'Female', 'other' => 'Other'),
            '#default_value' => !empty($formState->get('gender')) ? $formState->get('gender') : 'male',
        );

        $form['hobbies'] = array(
            '#type' => 'checkboxes',
            '#title' => 'Hobbies',
            '#options' => array('reading' => 'Reading', 'traveling' => 'Traveling', 'sports' => 'Sports'),
            '#default_value' => !empty($formState->get('hobbies')) ? $formState->get('hobbies') : array(),
        );

        $form['birth_date'] = array(
            '#type' => 'date',
            '#title' => 'Birth Date',
            '#required' => true,
            '#attributes' => array('class' => 'form-control', 'id' => 'birth_date'),
            '#description' => 'Please enter your birth date.',
            '#default_value' => !empty($formState->get('birth_date')) ? $formState->get('birth_date') : '',
        );

        $form['age'] = array(
            '#type' => 'number',
            '#title' => 'Age',
            '#required' => true,
            '#placeholder' => 'Age',
            '#attributes' => array('class' => 'form-control', 'id' => 'age', 'min' => 0),
            '#description' => 'Please enter your age.',
            '#default_value' => !empty($formState->get('age')) ? $formState->get('age') : '',
        );

        $form['resume'] = array(
            '#type' => 'file',
            '#title' => 'Upload Resume',
            '#attributes' => array('class' => 'form-control-file', 'id' => 'resume'),
            '#description' => 'Please upload your resume in PDF format.',
        );
        
        // How to make details field
        
        $form['personal_info'] = array(
            '#type' => 'details',
            '#title' => 'Personal Info',
            '#open' => true,
            'first_name' => $form['first_name'],
            'email' => $form['email'],
            'password' => $form['password'],
            'confirm_password' => $form['confirm_password'],
            'bio' => $form['bio'],
            'gender' => $form['gender'],
            'hobbies' => $form['hobbies'],
            'birth_date' => $form['birth_date'],
            'age' => $form['age'],
            'resume' => $form['resume'],
        );

       // How to add action buttons
    $form['submit'] => [
        '#type' => 'submit',
        '#value' => 'Submit',
        '#attributes' => ['class' => 'btn btn-primary'],
    ];
    $form['reset'] => [
        '#type' => 'reset',
        '#value' => 'Reset',
        '#attributes' => ['class' => 'btn btn-secondary'],
    ];

```