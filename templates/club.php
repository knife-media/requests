<!doctype html>
<html>
    <head>
        <meta charset="utf-8">

        <title>Заявка в клуб: #<?= $data->id; ?></title>

        <style>
            body {
                display: block;

                margin: 0;
                padding: 10px;

                font: normal 14px/1.4 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;

                background-color: #eee;
            }

            section {
                display: block;
                box-sizing: border-box;

                width: 100%;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;

                background-color: #fff;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);

                border: 1px solid #e5e5e5;
            }

            article {
                word-wrap: break-word;
            }

            header {
                display: grid;
                grid-template-columns: auto 1fr;
                grid-column-gap: 20px;
                grid-row-gap: 7px;

                margin: 10px 0 20px;
                padding: 20px 0;

                border: solid 1px #eee;
                border-left: 0;
                border-right: 0;
            }

            h1 {
                display: block;

                margin: 0;

                font-size: 23px;
                font-weight: 400;
            }
        </style>
    </head>

    <body>
        <section>
            <h1>Заявка в клуб: #<?= $data->id; ?></h1>

            <header>
                <?php
                    printf(
                        '<strong>Имя, род занятий:</strong> <span>%s</span>', htmlspecialchars($data->name)
                    );

                    printf(
                        '<strong>E-mail:</strong> <span>%s</span>', htmlspecialchars($data->email)
                    );

                    printf(
                        '<strong>Тема:</strong> <span>%s</span>', htmlspecialchars($data->subject)
                    );
                ?>
            </header>

            <article>
                <?php echo nl2br($data->text); ?>
            </article>
        </section>
    </body>
</html>
