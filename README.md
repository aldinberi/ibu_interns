# ibu_interns

The main objective of IBU Interns is the elimination of the aspect of paperwork by
digitalizing the process, in order to save time and make internships easily accessible
to students.

This project proposes and implements a robust system that enhances
internship efficiency by creating a web platform managed by the university. When
utilized in the context of Industrial training it allows companies to post internships,
for professors to approve them, interns to apply for them, post logs of work done
during the internship and finally for the company to grade them.

Entities: Interns, Professors, Companies, Internships, Logs

The system is implemented as a Web application
running a secure rest backend build using Flight PHP and single page frontend using jQuery and Bootstrap.

All backend requests are being validated, sanitized and filtered.

Layered architecture pattern is being used in combination with singlethon in the backend
and adapter design pattern in frontend.

Unit, inegration and system tests have been writen in PHPunit.

Some other notable features are: Using Google sign in for students and professors,
Using Have I Been Pwned for checking company passwords, SendGrid for sending mails,
hCaptcha for identifying if a user is a robot and JWT token for authorization.
