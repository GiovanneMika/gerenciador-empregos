<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class JobController extends Controller
{
    private const JOB_AREAS = [
        'Administração',
        'Agricultura',
        'Artes',
        'Atendimento ao Cliente',
        'Comercial',
        'Comunicação',
        'Construção Civil',
        'Consultoria',
        'Contabilidade',
        'Design',
        'Educação',
        'Engenharia',
        'Finanças',
        'Jurídica',
        'Logística',
        'Marketing',
        'Produção',
        'Recursos Humanos',
        'Saúde',
        'Segurança',
        'Tecnologia da Informação',
        'Telemarketing',
        'Vendas',
        'Outros',
    ];

    private const STATE_CODES = [
        'AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO',
    ];

    public function store(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser();
        if (!$user) {
            return $this->invalidTokenResponse();
        }

        if (!$user instanceof Company) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validator = Validator::make($request->all(), $this->jobRules());

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $data = $validator->validated();
        $data['state'] = strtoupper($data['state']);
        $data['company_id'] = $user->id;
        $data['contact'] = $user->email; // Usa o email da empresa autenticada

        JobVacancy::create($data);

        return response()->json(['message' => 'Created'], 201);
    }

    public function show(int $jobId): JsonResponse
    {
        $user = $this->authenticatedUser();
        if (!$user) {
            return $this->invalidTokenResponse();
        }

        $job = JobVacancy::with('company')->find($jobId);

        if (!$job) {
            return response()->json(['message' => 'Job not found'], 404);
        }

        return response()->json($this->formatJob($job));
    }

    public function search(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser();
        if (!$user) {
            return $this->invalidTokenResponse();
        }

        $filters = $this->extractFilters($request->input('filters'));
        $validator = $this->filterValidator($filters, allowCompany: true);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $query = $this->applyFilters(JobVacancy::with('company'), $validator->validated(), allowCompany: true);
        $jobs = $query->orderByDesc('created_at')->get();

        if ($jobs->isEmpty()) {
            return response()->json(['message' => 'Job not found'], 404);
        }

        return response()->json([
            'items' => $jobs->map(fn (JobVacancy $job) => $this->formatJob($job))->all(),
        ]);
    }

    public function listByCompany(Request $request, int $companyId): JsonResponse
    {
        $user = $this->authenticatedUser();
        if (!$user) {
            return $this->invalidTokenResponse();
        }

        if (!$user instanceof Company || $user->id !== $companyId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $filters = $this->extractFilters($request->input('filters'));
        $validator = $this->filterValidator($filters, allowCompany: false);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $query = $this->applyFilters(
            JobVacancy::with('company')->where('company_id', $companyId),
            $validator->validated(),
            allowCompany: false
        );

        $jobs = $query->orderByDesc('created_at')->get();

        if ($jobs->isEmpty()) {
            return response()->json(['message' => 'Job not found'], 404);
        }

        return response()->json([
            'items' => $jobs->map(fn (JobVacancy $job) => $this->formatJob($job))->all(),
        ]);
    }

    public function update(Request $request, int $jobId): JsonResponse
    {
        $user = $this->authenticatedUser();
        if (!$user) {
            return $this->invalidTokenResponse();
        }

        if (!$user instanceof Company) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $job = JobVacancy::where('company_id', $user->id)
            ->where('id', $jobId)
            ->first();

        if (!$job) {
            return response()->json(['message' => 'Job not found'], 404);
        }

        $validator = Validator::make($request->all(), $this->jobRules());

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $data = $validator->validated();
        $data['state'] = strtoupper($data['state']);
        $data['contact'] = $user->email; // Usa o email atual da empresa

        $job->update($data);

        return response()->json(['message' => 'Job updated successfully']);
    }

    public function destroy(int $jobId): JsonResponse
    {
        $user = $this->authenticatedUser();
        if (!$user) {
            return $this->invalidTokenResponse();
        }

        if (!$user instanceof Company) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $job = JobVacancy::where('company_id', $user->id)
            ->where('id', $jobId)
            ->first();

        if (!$job) {
            return response()->json(['message' => 'Job not found'], 404);
        }

        $job->delete();

        return response()->json(['message' => 'Job deleted successfully']);
    }

    public function apply(Request $request, int $jobId): JsonResponse
    {
        $user = $this->authenticatedUser();
        if (!$user) {
            return $this->invalidTokenResponse();
        }

        if (!$user instanceof User) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $job = JobVacancy::find($jobId);
        if (!$job) {
            return response()->json(['message' => 'Job not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:150',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|min:8|max:30|regex:/^[0-9]+$/',
            'education' => 'required|string|min:10|max:600',
            'experience' => 'required|string|min:10|max:600',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $data = $validator->validated();
        $data['name'] = strtoupper($data['name']);
        $data['email'] = $data['email'] ?? null;
        $data['phone'] = $data['phone'] ?? null;

        JobApplication::updateOrCreate(
            ['job_id' => $jobId, 'user_id' => $user->id],
            $data
        );

        return response()->json(['message' => 'Applied succesfully']);
    }

    public function sendFeedback(Request $request, int $jobId): JsonResponse
    {
        $user = $this->authenticatedUser();
        if (!$user) {
            return $this->invalidTokenResponse();
        }

        if (!$user instanceof Company) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $job = JobVacancy::where('company_id', $user->id)
            ->where('id', $jobId)
            ->first();
        if (!$job) {
            return response()->json(['message' => 'Job or User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|min:1',
            'message' => 'required|string|min:10|max:600',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $payload = $validator->validated();
        $candidate = User::find($payload['user_id']);

        if (!$candidate) {
            return response()->json(['message' => 'Job or User not found'], 404);
        }

        $application = JobApplication::where('job_id', $jobId)
            ->where('user_id', $candidate->id)
            ->first();

        if (!$application) {
            return response()->json(['message' => 'Job or User not found'], 404);
        }

        $application->update(['feedback' => $payload['message']]);

        return response()->json(['message' => 'Feedback sent successfully']);
    }

    public function listUserApplications(int $userId): JsonResponse
    {
        $user = $this->authenticatedUser();
        if (!$user) {
            return $this->invalidTokenResponse();
        }

        if (!$user instanceof User || $user->id !== $userId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $applications = JobApplication::with(['job.company'])
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'items' => $applications->map(function (JobApplication $application) {
                $job = $application->job;

                return [
                    'job_id' => $job?->id,
                    'title' => $job?->title,
                    'area' => $job?->area,
                    'company' => $job?->company?->name,
                    'description' => $job?->description,
                    'state' => $job?->state,
                    'city' => $job?->city,
                    'salary' => $job?->salary,
                    'contact' => $job?->contact,
                    'feedback' => $application->feedback,
                ];
            })->filter(fn ($item) => $item['job_id'] !== null)->values()->all(),
        ]);
    }

    public function listJobCandidates(int $companyId, int $jobId): JsonResponse
    {
        $user = $this->authenticatedUser();
        if (!$user) {
            return $this->invalidTokenResponse();
        }

        if (!$user instanceof Company || $user->id !== $companyId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $job = JobVacancy::where('company_id', $companyId)
            ->where('id', $jobId)
            ->first();
        if (!$job) {
            return response()->json(['message' => 'Job not found'], 404);
        }

        $candidates = $job->applications()->orderByDesc('created_at')->get();

        return response()->json([
            'items' => $candidates->map(fn (JobApplication $application) => [
                'user_id' => $application->user_id,
                'name' => $application->name,
                'email' => $application->email,
                'phone' => $application->phone,
                'education' => $application->education,
                'experience' => $application->experience,
                'feedback' => $application->feedback,
            ])->all(),
        ]);
    }

    private function authenticatedUser(): mixed
    {
        return auth('api')->user();
    }

    private function invalidTokenResponse(): JsonResponse
    {
        return response()->json(['message' => 'Invalid token'], 401);
    }

    private function jobRules(): array
    {
        return [
            'title' => 'required|string|min:3|max:150',
            'area' => ['required', 'string', Rule::in(self::JOB_AREAS)],
            'description' => 'required|string|min:10|max:5000',
            'state' => ['required', 'string', Rule::in(self::STATE_CODES)],
            'city' => 'required|string|min:2|max:150',
            'salary' => 'nullable|numeric|gt:0',
        ];
    }

    private function extractFilters(mixed $filters): array
    {
        if (!is_array($filters)) {
            return [];
        }

        if (isset($filters[0]) && is_array($filters[0])) {
            $filters = $filters[0];
        }

        return $filters;
    }

    private function filterValidator(array $filters, bool $allowCompany): ValidatorContract
    {
        if (isset($filters['state']) && is_string($filters['state'])) {
            $filters['state'] = strtoupper($filters['state']);
        }

        $rules = [
            'title' => 'nullable|string|min:1|max:150',
            'area' => ['nullable', 'string', Rule::in(self::JOB_AREAS)],
            'state' => ['nullable', 'string', Rule::in(self::STATE_CODES)],
            'city' => 'nullable|string|min:1|max:150',
            'salary_range.min' => 'nullable|numeric|gt:0',
            'salary_range.max' => 'nullable|numeric|gt:0|gte:salary_range.min',
        ];

        if ($allowCompany) {
            $rules['company'] = 'nullable|string|min:1|max:150';
        }

        return Validator::make($filters, $rules);
    }

    private function applyFilters(Builder $query, array $filters, bool $allowCompany): Builder
    {
        if (!empty($filters['title'])) {
            $query->where('title', 'like', '%' . $filters['title'] . '%');
        }

        if (!empty($filters['area'])) {
            $query->where('area', $filters['area']);
        }

        if (!empty($filters['state'])) {
            $query->where('state', strtoupper($filters['state']));
        }

        if (!empty($filters['city'])) {
            $query->where('city', 'like', '%' . $filters['city'] . '%');
        }

        if ($allowCompany && !empty($filters['company'])) {
            $query->whereHas('company', function ($builder) use ($filters) {
                $builder->where('name', 'like', '%' . $filters['company'] . '%');
            });
        }

        $salaryMin = data_get($filters, 'salary_range.min');
        $salaryMax = data_get($filters, 'salary_range.max');

        if ($salaryMin !== null && $salaryMin !== '') {
            $query->where('salary', '>=', $salaryMin);
        }

        if ($salaryMax !== null && $salaryMax !== '') {
            $query->where('salary', '<=', $salaryMax);
        }

        return $query;
    }

    private function formatJob(JobVacancy $job): array
    {
        return [
            'job_id' => $job->id,
            'title' => $job->title,
            'area' => $job->area,
            'description' => $job->description,
            'company' => $job->company?->name,
            'state' => $job->state,
            'city' => $job->city,
            'salary' => $job->salary,
            'contact' => $job->contact,
        ];
    }

    private function validationErrorResponse($validator): JsonResponse
    {
        $details = [];
        foreach ($validator->errors()->messages() as $field => $messages) {
            foreach ($messages as $message) {
                $details[] = ['field' => $field, 'error' => $message];
            }
        }

        return response()->json([
            'message' => 'Validation error',
            'code' => 'UNPROCESSABLE',
            'details' => $details,
        ], 422);
    }
}
