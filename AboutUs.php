<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="stylesheet" href="style.css" />
    <title>About Us - RU Thrifty</title>
    <!--This ensures that the picture and about us info is displayed as seen on the website-->
  </head>
  <body>
    <!--This has information about each member of the team along with their picture-->

    <section id="about-us" class="tab-content">
      <header>
        <h2>Meet our team</h2>
        <p class="intro">
          We are a diverse group of students who are passionate about making
          student life better.<br />Here's who we are:
        </p>
      </header>

      <!--This inserts the picture of each team member along with the required measurements for the photo-->
      <section class="members">
        <article class="team-member">
          <figure>
            <img
              src="team_member_pictures/nigel.jpg"
              alt="Nigel Qongo, Frontend Lead"
              width="300"
              height="300"
            />
            <figcaption style="text-align: center; margin-top: 5px">
              Nigel Qango
            </figcaption>
          </figure>
          <div class="team-info">
            <p>
              <strong>Nigel Qango - Frontend Lead</strong><br />
              Nigel specializes in creating intuitive and responsive user
              interfaces.<br />
              Expert in HTML, CSS, and modern frontend frameworks.<br />
              Focuses on delivering exceptional user experiences across all
              devices.
            </p>
          </div>
        </article>
        <article class="team-member">
          <figure>
            <img
              src="team_member_pictures/justin.jpg"
              alt="Justin Mudimbu, Backend Lead"
              width="300"
              height="300"
            />
            <figcaption style="text-align: center; margin-top: 5px">
              Justin Mudimbu
            </figcaption>
          </figure>

          <!--This paragraph explains a little bit about each team member and their role-->
          <div class="team-info">
            <p>
              <strong>Justin Mudimbu - Backend Lead</strong><br />
              Justin architects our server-side infrastructure.<br />
              Specializes in scalable system design and security
              implementation.<br />
              Ensures our platform remains robust and reliable.
            </p>
          </div>
        </article>

        <article class="team-member">
          <figure>
            <img
              src="team_member_pictures/charmaine.jpg"
              alt="Charmaine Chikengezha, Database Architect"
              width="300"
              height="300"
            />
            <figcaption style="text-align: center; margin-top: 5px">
              Charmaine Chikengezha
            </figcaption>
          </figure>
          <div class="team-info">
            <p>
              <strong>Charmaine - Database Architect</strong><br />
              Charmaine designs and optimizes our database structures.<br />
              Expert in data modeling and query optimization.<br />
              Ensures efficient data storage and retrieval for our growing
              platform.
            </p>
          </div>
        </article>

        <article class="team-member">
          <figure>
            <img
              src="team_member_pictures/karabo.jpg"
              alt="Karabo Mgwenya, Full Stack Developer"
              width="300"
              height="300"
            />
            <figcaption style="text-align: center; margin-top: 5px">
              Karabo Mgwenya
            </figcaption>
          </figure>
          <div class="team-info">
            <p>
              <strong>Karabo Mgwenya - Full Stack Developer</strong><br />
              Karabo leads our full stack development with expertise in both
              frontend and backend technologies.<br />
              Ensures seamless integration between user interface and
              server-side functionality.
            </p>
          </div>
        </article>
      </section>
    </section>
    <!-- Link to main Javascript File -->
    <script src="index.js" defer></script>
  </body>
</html>
