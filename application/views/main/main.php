<div class="container-fluid main-div d-flex align-items-center justify-content-center flex-column">
    <h1 class="text-light">Анализ файла Robots.txt</h1>
    <?php if(!empty($message)){?>
     <p class = 'text-danger h2'><?= $message ?></p>
    <?php }?>
    <div class="row d-flex justify-content-center align-middle">
        <div class="col-8 d-flex justify-content-center">
            <?php
                echo form_open("main/analysis", array('class' => 'd-flex justify-content-between'));
            ?>
                <input type="text" class="form-control search-field" name = "url"placeholder="Введите URL для анализа">
                <button type="submit" class="btn btn-success font-weight-bold main-page-button">Получить отчет</button>
            </form>
        </div>
    </div>
</div>