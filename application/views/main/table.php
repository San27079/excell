<div class="container-fluid">
    <div class ="row">
        <div class="col-12">
            <h1 class="text-center">Результаты проверки сайта</h1>
            <a class = "btn btn-success" href="/temp/file.xls">Скачать xls</a>
            <a class = "btn btn-success" href="/">Проверить еще</a>
        </div>
    </div>
    <br>
    <h4><?= $title?></h4>
    <table class="table">
        <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Название проверки</th>
            <th scope="col">Статус</th>
            <th scope="col"></th>
            <th scope="col">Текущее состояние</th>
        </tr>
        </thead>
        <tbody>
        <?php $i = 1; foreach ($file as $item): ?>
            <tr>
                <th scope="row"><?= $i++ ?></th>
                <td><?= $item['name'] ?></td>
                <td class="<?= $item['status_class']?> v_align"> <?= $item['status']?></td>
                <td>
                    <p>Статус:</p>
                    <p>Рекомендации:</p>
                </td>
                <td>
                    <p><?= $item['state']?></p>
                    <p><?= $item['recomendation']?></p>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>