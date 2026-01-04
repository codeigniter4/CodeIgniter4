<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SimcardModel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SimcardsController extends BaseController
{
    public function index()
    {
        $model = new SimcardModel();

        $status = $this->request->getGet('status');
        $search = $this->request->getGet('search');

        if ($status && in_array($status, ['free', 'sold'])) {
            $model->where('status', $status);
        }

        if ($search) {
            $model->like('number', $search);
        }

        $data = [
            'simcards' => $model->paginate(20),
            'pager'    => $model->pager,
            'status'   => $status,
            'search'   => $search
        ];

        return view('admin/simcards/index', $data);
    }

    public function create()
    {
        $model = new SimcardModel();
        $data = [
            'number' => $this->request->getPost('number'),
            'price'  => $this->request->getPost('price'),
            'status' => 'free',
        ];

        if ($model->save($data)) {
            return redirect()->to('/admin/simcards')->with('success', 'سیم‌کارت جدید با موفقیت اضافه شد.');
        } else {
            return redirect()->back()->with('error', 'خطا در افزودن سیم‌کارت.')->withInput();
        }
    }

    public function update($id)
    {
        $model = new SimcardModel();
        $data = [
            'id'    => $id,
            'price' => $this->request->getPost('price'),
        ];

        if ($model->save($data)) {
            return redirect()->to('/admin/simcards')->with('success', 'قیمت سیم‌کارت بروزرسانی شد.');
        } else {
            return redirect()->back()->with('error', 'خطا در ویرایش.')->withInput();
        }
    }

    public function delete($id)
    {
        $model = new SimcardModel();
        $simcard = $model->find($id);

        if ($simcard && $simcard['status'] == 'free') {
            $model->delete($id);
            return redirect()->to('/admin/simcards')->with('success', 'سیم‌کارت حذف شد.');
        } else {
            return redirect()->to('/admin/simcards')->with('error', 'فقط سیم‌کارت‌های آزاد قابل حذف هستند.');
        }
    }

    public function import()
    {
        $file = $this->request->getFile('excel_file');

        if (!$file->isValid()) {
            return redirect()->back()->with('error', 'فایل نامعتبر است.');
        }

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $model = new SimcardModel();
            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                // Skip header or empty rows if necessary, checking if first col looks like number
                if (empty($row[0])) continue;

                $number = trim((string)$row[0]);
                $price = isset($row[1]) ? (int)str_replace(',', '', (string)$row[1]) : 0;

                // Normalize number
                // If 10 digits and starts with 9, prepend 0
                if (preg_match('/^9\d{9}$/', $number)) {
                    $number = '0' . $number;
                }

                // Validate number format: 11 digits starting with 09
                if (!preg_match('/^09\d{9}$/', $number)) {
                    $errorCount++;
                    $errors[] = "سطر " . ($index + 1) . ": شماره نامعتبر ($number)";
                    continue;
                }

                // Check duplicate
                if ($model->where('number', $number)->countAllResults() > 0) {
                    $errorCount++;
                    $errors[] = "سطر " . ($index + 1) . ": شماره تکراری ($number)";
                    continue;
                }

                // Insert
                try {
                    $result = $model->insert([
                        'number' => $number,
                        'price'  => $price,
                        'status' => 'free',
                    ]);

                    if ($result === false) {
                        $errorCount++;
                        $errors[] = "سطر " . ($index + 1) . ": خطا در اعتبارسنجی ($number)";
                    } else {
                        $successCount++;
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "سطر " . ($index + 1) . ": خطا در ثبت ($number)";
                }
            }

            $report = "<h5>نتایج ایمپورت:</h5>";
            $report .= "<p class='text-success'>تعداد موفق: $successCount</p>";
            $report .= "<p class='text-danger'>تعداد خطا: $errorCount</p>";
            if (!empty($errors)) {
                $report .= "<ul>";
                foreach ($errors as $err) {
                    $report .= "<li>$err</li>";
                }
                $report .= "</ul>";
            }

            return redirect()->to('/admin/simcards')->with('import_report', $report);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'خطا در پردازش فایل اکسل: ' . $e->getMessage());
        }
    }
}
