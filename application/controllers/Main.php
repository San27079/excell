<?php
/**
 * Created by PhpStorm.
 * User: San27079
 * Date: 02.04.2018
 * Time: 19:26
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller
{

    public $loaded_file;


    public $name_test = [
        'status' => 'Проверка кода ответа сервера для файла robots.txt',
        'host' => 'Проверка указания директивы Host',
        'host_many' => 'Проверка количества директив Host, прописанных в файле',
        'robots' => 'Проверка наличия файла robots.txt',
        'robots_size' => 'Проверка размера файла robots.txt',
        'sitemap' => 'Проверка указания директивы Sitemap'
    ];

    public $status_classes = [
        0 => 'bg-danger',
        1 => 'bg-success'
    ];

    public $host_status = [
        0 => [
                'state'=> 'В файле robots.txt не указана директива Host',
                'recomendation' => 'Программист: Для того, чтобы поисковые системы знали, какая версия сайта является основных зеркалом, необходимо прописать адрес основного зеркала в директиве Host. В данный момент это не прописано. Необходимо добавить в файл robots.txt директиву Host. Директива Host задётся в файле 1 раз, после всех правил.'
            ],
        1 => [
                'state' => 'Директива Host указана',
                'recomendation' => 'Доработки не требуются'
        ]
    ];

    public $header_200_status = [
        0 => [
            'state'=> 'При обращении к файлу robots.txt сервер возвращает код ответа ',
            'recomendation' => 'Программист: Файл robots.txt должны отдавать код ответа 200, иначе файл не будет обрабатываться. Необходимо настроить сайт таким образом, чтобы при обращении к файлу robots.txt сервер возвращает код ответа 200'
        ],
        1 => [
            'state' => 'Файл robots.txt отдаёт код ответа сервера 200',
            'recomendation' => 'Доработки не требуются'
        ]
    ];

    public $host_many_status = [
        0 => [
            'state'=> 'В файле прописано несколько директив Host',
            'recomendation' => 'Программист: Директива Host должна быть указана в файле толоко 1 раз. Необходимо удалить все дополнительные директивы Host и оставить только 1, корректную и соответствующую основному зеркалу сайта'
        ],
        1 => [
            'state' => 'В файле прописана 1 директива Host',
            'recomendation' => 'Доработки не требуются'
        ]
    ];

    public $robots_status = [
        0 => [
            'state'=> 'Файл robots.txt отсутствует',
            'recomendation' => 'Программист: Создать файл robots.txt и разместить его на сайте.'
        ],
        1 => [
            'state' => 'Файл robots.txt присутствует',
            'recomendation' => 'Доработки не требуются'
        ]
    ];

    public $robots_size = [
        0 => [
            'state'=> 'Размера файла robots.txt составляет size, что превышает допустимую норму',
            'recomendation' => 'Программист: Максимально допустимый размер файла robots.txt составляем 32 кб. Необходимо отредактировть файл robots.txt таким образом, чтобы его размер не превышал 32 Кб'
        ],
        1 => [
            'state' => 'Размер файла robots.txt составляет size, что находится в пределах допустимой нормы',
            'recomendation' => 'Доработки не требуются'
        ]
    ];

    public $sitemap_status = [
        0 => [
            'state'=> 'В файле robots.txt не указана директива Sitemap',
            'recomendation' => 'Программист: Добавить в файл robots.txt директиву Sitemap'
        ],
        1 => [
            'state' => 'Директива Sitemap указана',
            'recomendation' => 'Доработки не требуются'
        ]
    ];

    public function __construct()
    {
        parent:: __construct();
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->library('PHPExcel');
    }

    public function index($message = '')
    {
        $data['title'] = 'Robots analysing';
        $data['message'] = $message;
        $this->load->view('layouts/header', $data);
        $this->load->view('main/main', $data);
        $this->load->view('layouts/footer', $data);
    }

    public function analysis()
    {
        $text = "Заполните url";
        $this->form_validation->set_rules('url', 'url', 'trim|required|min_length[3]','');
        if($this->form_validation->run() == FALSE){
            $text = "Заполните url";
            $this->index($text);
        }else {
            $url = rtrim($this->input->post('url'), '/').'/robots.txt';
            $url = str_replace('https://', 'http://', $url);
            $count = substr_count($url,'http://');
            if(!$count){
                $url = 'http://'.$url;
            }

            $this->table($url);
        }
    }

    public function table($url)
    {
        $data['title'] = 'Robots file analyse ' .$url;
        $data['file'][] = $this->check200($url)[0];

        if(!$this->check200($url)[1]){
            $this->render_table($data);
            exit;
        };
        $data['file'][] = $this->check_robots($url);
        $data['file'][] = $this->check_robots_size();
        $data['file'][] = $this->check_host()[0];
        if($this->check_host()[1]){
            $data['file'][] = $this->check_host_many();
        };
        $data['file'][] = $this->check_sitemap();

        $this->render_table($data);

    }

    private function render_table($data)
    {
        $this->create_excell($data);
        $this->load->view('layouts/header', $data);
        $this->load->view('main/table', $data);
        $this->load->view('layouts/footer', $data);
    }

    private function check_host()
    {
        $file = mb_strtolower($this->loaded_file);
        $count = substr_count($file,'host');

        $answer['name'] = $this->name_test['host'];
        $bool = true;
        if($count){
            $answer['state'] = $this->host_status['1']['state'];
            $answer['recomendation'] = $this->host_status['1']['recomendation'];
            $answer['status'] = 'Ок';
            $answer['status_class'] = $this->status_classes[1];
        }else{
            $answer['state'] = $this->host_status['0']['state'];
            $answer['recomendation'] = $this->host_status['0']['recomendation'];
            $answer['status'] = 'Ошибка';
            $answer['status_class'] = $this->status_classes[0];
            $bool = false;
        }
        return [$answer, $bool];

    }

    private function check_robots($url)
    {
        $this->loaded_file = file_get_contents($url);
        $answer['name'] = $this->name_test['robots'];
        if(!empty($this->loaded_file)){
            $answer['state'] = $this->robots_status['1']['state'];
            $answer['recomendation'] = $this->robots_status['1']['recomendation'];
            $answer['status'] = 'Ок';
            $answer['status_class'] = $this->status_classes[1];
        }else{
            $answer['state'] = $this->robots_status['0']['state'];
            $answer['recomendation'] = $this->robots_status['0']['recomendation'];
            $answer['status'] = 'Ошибка';
            $answer['status_class'] = $this->status_classes[0];
        }
        return $answer;
    }

    private function check200($url)
    {
        $status = get_headers($url);
        $status = explode(' ', $status[0])[1];
        $answer_200['name'] = $this->name_test['status'];
        $bool = true;
        if($status == 200){
            $answer_200['state'] = $this->header_200_status['1']['state'];
            $answer_200['recomendation'] = $this->header_200_status['1']['recomendation'];
            $answer_200['status'] = 'Ок';
            $answer_200['status_class'] = $this->status_classes[1];
        }else{
            $answer_200['state'] = $this->header_200_status['0']['state'].$status;
            $answer_200['recomendation'] = $this->header_200_status['0']['recomendation'];
            $answer_200['status'] = 'Ошибка';
            $answer_200['status_class'] = $this->status_classes[0];
            $bool = false;
        }
        return [$answer_200, $bool];
    }

    private function check_sitemap()
    {
        $file = mb_strtolower($this->loaded_file);
        $count = substr_count($file,'sitemap');

        $answer['name'] = $this->name_test['sitemap'];

        if($count){
            $answer['state'] = $this->sitemap_status['1']['state'];
            $answer['recomendation'] = $this->sitemap_status['1']['recomendation'];
            $answer['status'] = 'Ок';
            $answer['status_class'] = $this->status_classes[1];
        }else{
            $answer['state'] = $this->sitemap_status['0']['state'];
            $answer['recomendation'] = $this->sitemap_status['0']['recomendation'];
            $answer['status'] = 'Ошибка';
            $answer['status_class'] = $this->status_classes[0];
        }
        return $answer;
    }

    private function check_host_many()
    {
        $file = mb_strtolower($this->loaded_file);
        $count = substr_count($file,'host');

        $answer['name'] = $this->name_test['host_many'];

        if($count == 1){
            $answer['state'] = $this->host_many_status['1']['state'];
            $answer['recomendation'] = $this->host_many_status['1']['recomendation'];
            $answer['status'] = 'Ок';
            $answer['status_class'] = $this->status_classes[1];
        }else{
            $answer['state'] = $this->host_many_status['0']['state'];
            $answer['recomendation'] = $this->host_many_status['0']['recomendation'];
            $answer['status'] = 'Ошибка';
            $answer['status_class'] = $this->status_classes[0];
        }
        return $answer;
    }

    private function check_robots_size()
    {
        $fsize = strlen($this->loaded_file)/1000;
        $answer['name'] = $this->name_test['robots_size'];
        if($fsize < 32){
            $answer['state'] = $this->robots_size['1']['state'];
            $answer['recomendation'] = $this->robots_size['1']['recomendation'];
            $answer['state'] = str_replace('size', $fsize.' КБайта', $answer['state']);
            $answer['status'] = 'Ок';
            $answer['status_class'] = $this->status_classes[1];
        }else{
            $answer['state'] = $this->robots_size['0']['state'];
            $answer['recomendation'] = $this->robots_size['0']['recomendation'];
            $answer['state'] = str_replace('size', $fsize.' КБайта', $answer['state']);
            $answer['status'] = 'Ошибка';
            $answer['status_class'] = $this->status_classes[0];
        }
        return $answer;
    }

    public function create_excell($data)
    {
        $xls = new PHPExcel();
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $sheet->setTitle('robots');

        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->setCellValue("A1", $data['title']);
        $sheet->setCellValue("A2", '№');
        $sheet->setCellValue("B2", 'Название проверки');
        $sheet->setCellValue("C2", 'Статус');
        $sheet->setCellValue("D2", '');
        $sheet->setCellValue("E2", 'Текущее состояние');

        $color_head = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => '85A4D3')
            )
        );

        $color_red = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => '913034')
            )
        );

        $color_green = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => '00913E')
            )
        );

        $sheet->getStyle('A2')->applyFromArray($color_head);
        $sheet->getStyle('B2')->applyFromArray($color_head);
        $sheet->getStyle('C2')->applyFromArray($color_head);
        $sheet->getStyle('D2')->applyFromArray($color_head);
        $sheet->getStyle('E2')->applyFromArray($color_head);

        $i = 3;
        $j = 1;

        foreach($data['file'] as $item){
            $sheet->mergeCells('A'.$i.':A'.($i+1));
            $sheet->mergeCells('B'.$i.':B'.($i+1));
            $sheet->mergeCells('C'.$i.':C'.($i+1));

            $sheet->getRowDimension($i)->setRowHeight(30);

            $sheet->getColumnDimension('A')->setWidth(7);
            $sheet->getColumnDimension('B')->setWidth(30);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(60);

            $sheet->setCellValue("A".$i, $j);
            $sheet->setCellValue("B".$i, $item['name']);
            $sheet->setCellValue("C".$i, $item['status']);
            $sheet->setCellValue("D".$i, 'Статус:');
            $sheet->setCellValue("D".($i+1), 'Рекомендации:');
            $sheet->setCellValue("E".$i, $item['state']);
            $sheet->setCellValue("E".($i+1), $item['recomendation']);

            //set styles
            $sheet->getStyle("A".$i)->getAlignment()->setWrapText(true);
            $sheet->getStyle("B".$i)->getAlignment()->setWrapText(true);
            $sheet->getStyle("C".$i)->getAlignment()->setWrapText(true);
            $sheet->getStyle("D".$i)->getAlignment()->setWrapText(true);
            $sheet->getStyle("E".$i)->getAlignment()->setWrapText(true);
            $sheet->getStyle("D".($i+1))->getAlignment()->setWrapText(true);
            $sheet->getStyle("E".($i+1))->getAlignment()->setWrapText(true);

            $sheet->getStyle("A".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D".($i+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle("A".$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $sheet->getStyle("B".$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $sheet->getStyle("C".$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $sheet->getStyle("D".$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $sheet->getStyle("D".($i+1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            //colors

            $sheet->getStyle('A'.($i+2))->applyFromArray($color_head);
            $sheet->getStyle('B'.($i+2))->applyFromArray($color_head);
            $sheet->getStyle('C'.($i+2))->applyFromArray($color_head);
            $sheet->getStyle('D'.($i+2))->applyFromArray($color_head);
            $sheet->getStyle('E'.($i+2))->applyFromArray($color_head);

            if(!strnatcasecmp($item['status_class'], 'bg-danger')){
                $sheet->getStyle('C'.($i))->applyFromArray($color_red);
            }else{
                $sheet->getStyle('C'.($i))->applyFromArray($color_green);
            }

            $i += 3;
            $j++;
        }
        $objWriter = new PHPExcel_Writer_Excel5($xls);
        if(file_exists($_SERVER['DOCUMENT_ROOT'].'/temp/file.xls')){
            unlink($_SERVER['DOCUMENT_ROOT'].'/temp/file.xls');
        }
        $objWriter->save($_SERVER['DOCUMENT_ROOT'].'/temp/file.xls');
    }
}