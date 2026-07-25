<?php

namespace App\Http\Controllers;

use App\Exports\GenericReportExport;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService)
    {
    }

    // --- Client reports ---------------------------------------------------

    public function clients(Request $request): View
    {
        return $this->showReport(
            $this->reportService->clientsReport($request->string('status')->toString() ?: null),
            'reports.clients',
            'status',
            ['All', 'Active', 'Inactive']
        );
    }

    public function clientsPrint(Request $request): View
    {
        return $this->printReport($this->reportService->clientsReport($request->string('status')->toString() ?: null));
    }

    public function clientsExport(Request $request, string $format): BinaryFileResponse|Response
    {
        return $this->export($this->reportService->clientsReport($request->string('status')->toString() ?: null), $format);
    }

    // --- Plot reports -------------------------------------------------------

    public function plots(Request $request): View
    {
        return $this->showReport(
            $this->reportService->plotsReport($request->string('status')->toString() ?: null),
            'reports.plots',
            'status',
            ['All', 'Available', 'Reserved', 'Sold', 'Cancelled']
        );
    }

    public function plotsPrint(Request $request): View
    {
        return $this->printReport($this->reportService->plotsReport($request->string('status')->toString() ?: null));
    }

    public function plotsExport(Request $request, string $format): BinaryFileResponse|Response
    {
        return $this->export($this->reportService->plotsReport($request->string('status')->toString() ?: null), $format);
    }

    // --- Block reports ------------------------------------------------------

    public function blocks(): View
    {
        return $this->showReport($this->reportService->blockSummaryReport(), 'reports.blocks', 'none');
    }

    public function blocksPrint(): View
    {
        return $this->printReport($this->reportService->blockSummaryReport());
    }

    public function blocksExport(string $format): BinaryFileResponse|Response
    {
        return $this->export($this->reportService->blockSummaryReport(), $format);
    }

    // --- Financial reports ---------------------------------------------------

    public function collection(Request $request): View
    {
        return $this->showReport(
            $this->reportService->collectionReport($request->input('from'), $request->input('to')),
            'reports.collection',
            'date-range'
        );
    }

    public function collectionPrint(Request $request): View
    {
        return $this->printReport($this->reportService->collectionReport($request->input('from'), $request->input('to')));
    }

    public function collectionExport(Request $request, string $format): BinaryFileResponse|Response
    {
        return $this->export($this->reportService->collectionReport($request->input('from'), $request->input('to')), $format);
    }

    public function outstanding(): View
    {
        return $this->showReport($this->reportService->outstandingBalancesReport(), 'reports.outstanding', 'none');
    }

    public function outstandingPrint(): View
    {
        return $this->printReport($this->reportService->outstandingBalancesReport());
    }

    public function outstandingExport(string $format): BinaryFileResponse|Response
    {
        return $this->export($this->reportService->outstandingBalancesReport(), $format);
    }

    // --- Installment reports -------------------------------------------------

    public function dueInstallments(Request $request): View
    {
        return $this->showReport(
            $this->reportService->dueInstallmentsReport($request->input('from'), $request->input('to')),
            'reports.due-installments',
            'date-range'
        );
    }

    public function dueInstallmentsPrint(Request $request): View
    {
        return $this->printReport($this->reportService->dueInstallmentsReport($request->input('from'), $request->input('to')));
    }

    public function dueInstallmentsExport(Request $request, string $format): BinaryFileResponse|Response
    {
        return $this->export($this->reportService->dueInstallmentsReport($request->input('from'), $request->input('to')), $format);
    }

    public function defaulters(): View
    {
        return $this->showReport($this->reportService->defaultersReport(), 'reports.defaulters', 'none');
    }

    public function defaultersPrint(): View
    {
        return $this->printReport($this->reportService->defaultersReport());
    }

    public function defaultersExport(string $format): BinaryFileResponse|Response
    {
        return $this->export($this->reportService->defaultersReport(), $format);
    }

    // --- Sales reports --------------------------------------------------------

    public function sales(Request $request): View
    {
        return $this->showReport(
            $this->reportService->salesReport($request->input('from'), $request->input('to')),
            'reports.sales',
            'date-range'
        );
    }

    public function salesPrint(Request $request): View
    {
        return $this->printReport($this->reportService->salesReport($request->input('from'), $request->input('to')));
    }

    public function salesExport(Request $request, string $format): BinaryFileResponse|Response
    {
        return $this->export($this->reportService->salesReport($request->input('from'), $request->input('to')), $format);
    }

    // --- Shared rendering helpers ----------------------------------------------

    /**
     * @param  'status'|'date-range'|'none'  $filterType
     */
    private function showReport(array $report, string $routeBase, string $filterType, array $statusOptions = []): View
    {
        return view('reports.show', [
            ...$report,
            'routeBase' => $routeBase,
            'filterType' => $filterType,
            'statusOptions' => $statusOptions,
        ]);
    }

    private function printReport(array $report): View
    {
        return view('reports.print', $report);
    }

    /**
     * Excel and PDF share one export entry point since both just need the
     * same report array -- the only difference is which library renders it.
     */
    private function export(array $report, string $format): BinaryFileResponse|Response
    {
        if ($format === 'excel') {
            return Excel::download(new GenericReportExport($report), $report['filename'].'.xlsx');
        }

        $pdf = Pdf::loadView('reports.pdf', $report)->setPaper('a4', 'landscape');

        return $pdf->download($report['filename'].'.pdf');
    }
}
