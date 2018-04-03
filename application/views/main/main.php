<div class="container-fluid main-div d-flex align-items-center justify-content-center flex-column">
    <h1 class="text-light">Анализ файла Robots.txt</h1>
    <?php if(!empty($message)){?>
     <p class = 'text-danger h2'><?= $message ?></p>
    <?php }?>
    <div class="row d-flex justify-content-center align-middle">
            <?php
                echo form_open("main/analysis", array('class' => 'form-inline'));
            ?>
                <input type="text" class="form-control search-field" size="35" name = "url"placeholder="Введите http://... для анализа">
                <br>
                <button type="submit" class="btn btn-success font-weight-bold">Получить отчет</button>
            </form>
    </div>
</div>