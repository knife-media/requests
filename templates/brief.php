<!doctype html>
<html>
    <head>
        <meta charset="utf-8">

        <title>Форма обратной связи: #<?= $data->id; ?></title>

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

            h1 {
                display: block;

                margin: 0 0 20px;
                padding-bottom: 10px;

                font-size: 23px;
                font-weight: 400;

                border-bottom: solid 1px #eee;
            }

            h4 {
                display: block;
                margin: 30px 0 0;
            }

            p {
                margin-top: 10px;
            }
        </style>
    </head>

    <body>
        <section>
            <h1>Форма обратной связи #<?= $data->id; ?></h1>

            <article>
                <?php
                    foreach ($data->fields as $field) {
                        printf(
                            '<h4>%s</h4>', htmlspecialchars($field['label'])
                        );

                        printf(
                            '<p>%s</p>', htmlspecialchars($field['value'])
                        );
                    }
                ?>

                <?php if (!empty($data->formats)) : ?>
                    <h4>Выбранные форматы</h4>
                    <ul>
                        <?php
                            foreach ($data->formats as $format) {
                                printf(
                                    '<li>%s</li>', htmlspecialchars($format)
                                );
                            }
                        ?>
                    </ul>
                <?php endif; ?>
            </article>
        </section>
    </body>
</html>
